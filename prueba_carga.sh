#!/usr/bin/env bash
# Prueba de carga y concurrencia - Sistema de Farmacia (Informe N3)
#
# Parte 1: carga de lectura (dashboard, inventario, ventas) con Apache Bench (ab).
# Parte 2: concurrencia de ventas sobre el ultimo stock (no debe haber sobreventa).
#
# REQUISITOS
#   - Servidor con varios procesos (el de un solo proceso serializa las peticiones):
#       PHP_CLI_SERVER_WORKERS=8 php artisan serve
#   - Usuario con rol Tecnico (o Quimico) que ya tenga la CAJA ABIERTA en la UI.
#   - Un producto de venta libre con stock en la ubicacion de venta (vitrina) y
#     un lote vigente. Anota el stock inicial (por ejemplo 5 unidades).
#   - curl y GNU grep. Para la parte 1: ab (Arch: paquete "apache"; Debian: apache2-utils).
#
# USO
#   EMAIL=tecnico@ejemplo.cl PASSWORD=clave PRODUCTO_ID=12 bash prueba_carga.sh
#
# VARIABLES OPCIONALES
#   BASE (http://127.0.0.1:8000)   LECTURAS (300)   CONCURRENCIA (20)   VENTAS_PARALELAS (20)
#
# La salida queda en evidencia_carga_<fecha>.txt (se adjunta al informe).

set -u

BASE="${BASE:-http://127.0.0.1:8000}"
EMAIL="${EMAIL:?Define EMAIL}"
PASSWORD="${PASSWORD:?Define PASSWORD}"
PRODUCTO_ID="${PRODUCTO_ID:?Define PRODUCTO_ID}"
LECTURAS="${LECTURAS:-300}"
CONCURRENCIA="${CONCURRENCIA:-20}"
VENTAS_PARALELAS="${VENTAS_PARALELAS:-20}"

SALIDA="evidencia_carga_$(date +%Y%m%d_%H%M%S).txt"
JAR="$(mktemp)"
TMP="$(mktemp)"
exec > >(tee "$SALIDA") 2>&1

echo "=== Prueba de carga y concurrencia ==="
echo "Fecha:        $(date '+%d-%m-%Y %H:%M:%S')"
echo "Servidor:     $BASE"
echo "Lecturas:     $LECTURAS peticiones, concurrencia $CONCURRENCIA"
echo "Ventas:       $VENTAS_PARALELAS peticiones simultaneas de 1 unidad (producto $PRODUCTO_ID)"
echo

# ---------------------------------------------------------------- login
TOKEN="$(curl -s -c "$JAR" "$BASE/login" | grep -oP 'name="_token" value="\K[^"]+' | head -1)"
if [ -z "$TOKEN" ]; then
  echo "ERROR: no se pudo obtener el token CSRF. Revisa que el servidor este arriba en $BASE."
  exit 1
fi

curl -s -b "$JAR" -c "$JAR" -o /dev/null \
  --data-urlencode "_token=$TOKEN" \
  --data-urlencode "email=$EMAIL" \
  --data-urlencode "password=$PASSWORD" \
  "$BASE/login"

CODIGO="$(curl -s -b "$JAR" -o /dev/null -w '%{http_code}' "$BASE/dashboard")"
if [ "$CODIGO" != "200" ]; then
  echo "ERROR: el login no funciono (dashboard respondio $CODIGO). Revisa EMAIL y PASSWORD."
  exit 1
fi
echo "Login correcto."
echo

# ---------------------------------------------------------------- parte 1
echo "=== PARTE 1: carga de lectura ==="
COOKIES="$(awk 'NF>=7 {printf "%s%s=%s", sep, $6, $7; sep="; "}' "$JAR")"

if command -v ab >/dev/null 2>&1; then
  for RUTA in /dashboard /inventario /ventas; do
    echo "--- GET $RUTA ---"
    ab -q -n "$LECTURAS" -c "$CONCURRENCIA" -H "Cookie: $COOKIES" "$BASE$RUTA" 2>&1 \
      | grep -E "Requests per second|Time per request|Failed requests|Non-2xx|^ +(50|95|99|100)%"
    echo
  done
else
  echo "ab no esta instalado: se omite la parte 1 (instala apache2-utils o apache)."
  echo
fi

# ---------------------------------------------------------------- parte 2
echo "=== PARTE 2: concurrencia de ventas sobre el ultimo stock ==="
INICIO="$(date '+%Y-%m-%d %H:%M:%S')"
TOKEN_VENTA="$(curl -s -b "$JAR" -c "$JAR" "$BASE/ventas/crear" | grep -oP 'name="_token" value="\K[^"]+' | head -1)"

if [ -z "$TOKEN_VENTA" ]; then
  echo "ERROR: no se pudo abrir /ventas/crear con este usuario."
  exit 1
fi

vender() {
  curl -s -b "$JAR" -o /dev/null -w "%{http_code} %{redirect_url}\n" \
    -d "_token=$TOKEN_VENTA" \
    -d "metodo_pago=efectivo" \
    -d "tipo_documento=boleta" \
    -d "detalles[0][producto_id]=$PRODUCTO_ID" \
    -d "detalles[0][cantidad]=1" \
    "$BASE/ventas"
}
export -f vender
export JAR TOKEN_VENTA BASE PRODUCTO_ID

seq "$VENTAS_PARALELAS" | xargs -P "$VENTAS_PARALELAS" -I{} bash -c vender > "$TMP"

TOTAL="$(wc -l < "$TMP")"
EXITOSAS="$(grep -cE ' .*/ventas/[0-9]+$' "$TMP")"
echo "Peticiones enviadas:                 $TOTAL"
echo "Ventas aceptadas (redirigen a boleta): $EXITOSAS"
echo "Ventas rechazadas:                   $((TOTAL - EXITOSAS))"
echo "Inicio de la prueba:                 $INICIO"
echo
echo "VERIFICACION EN LA BASE DE DATOS (psql -h 127.0.0.1 -U farmacia -d farmacia):"
echo "  SELECT COALESCE(SUM(cantidad),0) AS stock_restante FROM stock WHERE producto_id = $PRODUCTO_ID;"
echo "  SELECT COUNT(*) AS ventas_creadas FROM ventas WHERE created_at >= '$INICIO';"
echo
echo "RESULTADO ESPERADO:"
echo "  - ventas_creadas = ventas aceptadas, y nunca mayor que el stock inicial."
echo "  - stock_restante = stock inicial - ventas aceptadas, y nunca negativo."
echo "  - Si el stock inicial era menor que $VENTAS_PARALELAS, el resto debe quedar rechazado."

rm -f "$JAR" "$TMP"
echo
echo "Evidencia guardada en $SALIDA"
