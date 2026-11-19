# Resultado de pruebas de acceso por rol

Ejecución: 17-11-2026 00:41

| Caso | Grupo de rutas | Actor | Petición | Esperado | Obtenido | Resultado |
|---|---|---|---|---|---|---|
| PT-001 | R01 Recetas y médicos | Administrador | GET /recetas | permitido (no 403) | 200 | OK |
| PT-006 | R02 Aprobar / rechazar receta | Administrador | POST /recetas/1/aprobar | 403 | 403 | OK |
| PT-011 | R03 Dashboard | Administrador | GET /dashboard | permitido (no 403) | 200 | OK |
| PT-016 | R04 Perfil | Administrador | GET /profile | permitido (no 403) | 200 | OK |
| PT-021 | R05 Productos | Administrador | GET /productos | 403 | 403 | OK |
| PT-026 | R06 Ventas | Administrador | GET /ventas | 403 | 403 | OK |
| PT-031 | R07 Usuarios | Administrador | GET /usuarios | permitido (no 403) | 200 | OK |
| PT-036 | R08 Descuadres de stock controlado | Administrador | GET /descuadres-controlados | 403 | 403 | OK |
| PT-041 | R09 Convenios y coberturas | Administrador | GET /convenios | permitido (no 403) | 200 | OK |
| PT-046 | R10 Lotes | Administrador | GET /lotes | 403 | 403 | OK |
| PT-051 | R11 Inventario - consulta | Administrador | GET /inventario | permitido (no 403) | 200 | OK |
| PT-056 | R12 Próximos a vencer y consulta de canjes | Administrador | GET /inventario/proximos-vencer | permitido (no 403) | 200 | OK |
| PT-061 | R13 Canjes - solicitar | Administrador | GET /inventario/canjes/solicitar/1 | permitido (no 403) | 404 | OK |
| PT-066 | R14 Canjes - aprobar / rechazar | Administrador | POST /inventario/canjes/1/aprobar | 403 | 403 | OK |
| PT-071 | R15 Canjes - entregas, devoluciones y cierre | Administrador | POST /inventario/canjes/1/cerrar | permitido (no 403) | 404 | OK |
| PT-076 | R16 Inventario - transferir, ajustar, marcar no apto | Administrador | GET /inventario/ajustar | permitido (no 403) | 200 | OK |
| PT-081 | R17 Caja | Administrador | GET /caja | permitido (no 403) | 200 | OK |
| PT-086 | R18 Pacientes | Administrador | GET /pacientes | permitido (no 403) | 200 | OK |
| PT-091 | R19 Compras y recepciones - consulta | Administrador | GET /compras | 403 | 403 | OK |
| PT-096 | R20 Compras - crear orden | Administrador | GET /compras/crear | 403 | 403 | OK |
| PT-101 | R21 Proveedores | Administrador | GET /compras/proveedores | 403 | 403 | OK |
| PT-106 | R22 Recepciones - recibir mercadería | Administrador | GET /compras/1/recibir | 403 | 403 | OK |
| PT-002 | R01 Recetas y médicos | Químico Farmacéutico | GET /recetas | permitido (no 403) | 200 | OK |
| PT-007 | R02 Aprobar / rechazar receta | Químico Farmacéutico | POST /recetas/1/aprobar | permitido (no 403) | 404 | OK |
| PT-012 | R03 Dashboard | Químico Farmacéutico | GET /dashboard | permitido (no 403) | 200 | OK |
| PT-017 | R04 Perfil | Químico Farmacéutico | GET /profile | permitido (no 403) | 200 | OK |
| PT-022 | R05 Productos | Químico Farmacéutico | GET /productos | permitido (no 403) | 200 | OK |
| PT-027 | R06 Ventas | Químico Farmacéutico | GET /ventas | permitido (no 403) | 200 | OK |
| PT-032 | R07 Usuarios | Químico Farmacéutico | GET /usuarios | 403 | 403 | OK |
| PT-037 | R08 Descuadres de stock controlado | Químico Farmacéutico | GET /descuadres-controlados | permitido (no 403) | 200 | OK |
| PT-042 | R09 Convenios y coberturas | Químico Farmacéutico | GET /convenios | permitido (no 403) | 200 | OK |
| PT-047 | R10 Lotes | Químico Farmacéutico | GET /lotes | permitido (no 403) | 200 | OK |
| PT-052 | R11 Inventario - consulta | Químico Farmacéutico | GET /inventario | permitido (no 403) | 200 | OK |
| PT-057 | R12 Próximos a vencer y consulta de canjes | Químico Farmacéutico | GET /inventario/proximos-vencer | permitido (no 403) | 200 | OK |
| PT-062 | R13 Canjes - solicitar | Químico Farmacéutico | GET /inventario/canjes/solicitar/1 | 403 | 403 | OK |
| PT-067 | R14 Canjes - aprobar / rechazar | Químico Farmacéutico | POST /inventario/canjes/1/aprobar | permitido (no 403) | 302 | OK |
| PT-072 | R15 Canjes - entregas, devoluciones y cierre | Químico Farmacéutico | POST /inventario/canjes/1/cerrar | permitido (no 403) | 404 | OK |
| PT-077 | R16 Inventario - transferir, ajustar, marcar no apto | Químico Farmacéutico | GET /inventario/ajustar | 403 | 403 | OK |
| PT-082 | R17 Caja | Químico Farmacéutico | GET /caja | 403 | 403 | OK |
| PT-087 | R18 Pacientes | Químico Farmacéutico | GET /pacientes | permitido (no 403) | 200 | OK |
| PT-092 | R19 Compras y recepciones - consulta | Químico Farmacéutico | GET /compras | permitido (no 403) | 200 | OK |
| PT-097 | R20 Compras - crear orden | Químico Farmacéutico | GET /compras/crear | permitido (no 403) | 200 | OK |
| PT-102 | R21 Proveedores | Químico Farmacéutico | GET /compras/proveedores | permitido (no 403) | 200 | OK |
| PT-107 | R22 Recepciones - recibir mercadería | Químico Farmacéutico | GET /compras/1/recibir | 403 | 403 | OK |
| PT-003 | R01 Recetas y médicos | Técnico Farmacéutico | GET /recetas | permitido (no 403) | 200 | OK |
| PT-008 | R02 Aprobar / rechazar receta | Técnico Farmacéutico | POST /recetas/1/aprobar | 403 | 403 | OK |
| PT-013 | R03 Dashboard | Técnico Farmacéutico | GET /dashboard | permitido (no 403) | 200 | OK |
| PT-018 | R04 Perfil | Técnico Farmacéutico | GET /profile | permitido (no 403) | 200 | OK |
| PT-023 | R05 Productos | Técnico Farmacéutico | GET /productos | permitido (no 403) | 200 | OK |
| PT-028 | R06 Ventas | Técnico Farmacéutico | GET /ventas | permitido (no 403) | 200 | OK |
| PT-033 | R07 Usuarios | Técnico Farmacéutico | GET /usuarios | 403 | 403 | OK |
| PT-038 | R08 Descuadres de stock controlado | Técnico Farmacéutico | GET /descuadres-controlados | 403 | 403 | OK |
| PT-043 | R09 Convenios y coberturas | Técnico Farmacéutico | GET /convenios | 403 | 403 | OK |
| PT-048 | R10 Lotes | Técnico Farmacéutico | GET /lotes | permitido (no 403) | 200 | OK |
| PT-053 | R11 Inventario - consulta | Técnico Farmacéutico | GET /inventario | permitido (no 403) | 200 | OK |
| PT-058 | R12 Próximos a vencer y consulta de canjes | Técnico Farmacéutico | GET /inventario/proximos-vencer | permitido (no 403) | 200 | OK |
| PT-063 | R13 Canjes - solicitar | Técnico Farmacéutico | GET /inventario/canjes/solicitar/1 | permitido (no 403) | 404 | OK |
| PT-068 | R14 Canjes - aprobar / rechazar | Técnico Farmacéutico | POST /inventario/canjes/1/aprobar | 403 | 403 | OK |
| PT-073 | R15 Canjes - entregas, devoluciones y cierre | Técnico Farmacéutico | POST /inventario/canjes/1/cerrar | permitido (no 403) | 404 | OK |
| PT-078 | R16 Inventario - transferir, ajustar, marcar no apto | Técnico Farmacéutico | GET /inventario/ajustar | permitido (no 403) | 200 | OK |
| PT-083 | R17 Caja | Técnico Farmacéutico | GET /caja | permitido (no 403) | 200 | OK |
| PT-088 | R18 Pacientes | Técnico Farmacéutico | GET /pacientes | permitido (no 403) | 200 | OK |
| PT-093 | R19 Compras y recepciones - consulta | Técnico Farmacéutico | GET /compras | 403 | 403 | OK |
| PT-098 | R20 Compras - crear orden | Técnico Farmacéutico | GET /compras/crear | 403 | 403 | OK |
| PT-103 | R21 Proveedores | Técnico Farmacéutico | GET /compras/proveedores | 403 | 403 | OK |
| PT-108 | R22 Recepciones - recibir mercadería | Técnico Farmacéutico | GET /compras/1/recibir | 403 | 403 | OK |
| PT-004 | R01 Recetas y médicos | Bodeguero | GET /recetas | 403 | 403 | OK |
| PT-009 | R02 Aprobar / rechazar receta | Bodeguero | POST /recetas/1/aprobar | 403 | 403 | OK |
| PT-014 | R03 Dashboard | Bodeguero | GET /dashboard | permitido (no 403) | 200 | OK |
| PT-019 | R04 Perfil | Bodeguero | GET /profile | permitido (no 403) | 200 | OK |
| PT-024 | R05 Productos | Bodeguero | GET /productos | 403 | 403 | OK |
| PT-029 | R06 Ventas | Bodeguero | GET /ventas | 403 | 403 | OK |
| PT-034 | R07 Usuarios | Bodeguero | GET /usuarios | 403 | 403 | OK |
| PT-039 | R08 Descuadres de stock controlado | Bodeguero | GET /descuadres-controlados | 403 | 403 | OK |
| PT-044 | R09 Convenios y coberturas | Bodeguero | GET /convenios | 403 | 403 | OK |
| PT-049 | R10 Lotes | Bodeguero | GET /lotes | permitido (no 403) | 200 | OK |
| PT-054 | R11 Inventario - consulta | Bodeguero | GET /inventario | 403 | 403 | OK |
| PT-059 | R12 Próximos a vencer y consulta de canjes | Bodeguero | GET /inventario/proximos-vencer | permitido (no 403) | 200 | OK |
| PT-064 | R13 Canjes - solicitar | Bodeguero | GET /inventario/canjes/solicitar/1 | permitido (no 403) | 404 | OK |
| PT-069 | R14 Canjes - aprobar / rechazar | Bodeguero | POST /inventario/canjes/1/aprobar | 403 | 403 | OK |
| PT-074 | R15 Canjes - entregas, devoluciones y cierre | Bodeguero | POST /inventario/canjes/1/cerrar | permitido (no 403) | 404 | OK |
| PT-079 | R16 Inventario - transferir, ajustar, marcar no apto | Bodeguero | GET /inventario/ajustar | permitido (no 403) | 200 | OK |
| PT-084 | R17 Caja | Bodeguero | GET /caja | 403 | 403 | OK |
| PT-089 | R18 Pacientes | Bodeguero | GET /pacientes | 403 | 403 | OK |
| PT-094 | R19 Compras y recepciones - consulta | Bodeguero | GET /compras | permitido (no 403) | 200 | OK |
| PT-099 | R20 Compras - crear orden | Bodeguero | GET /compras/crear | 403 | 403 | OK |
| PT-104 | R21 Proveedores | Bodeguero | GET /compras/proveedores | 403 | 403 | OK |
| PT-109 | R22 Recepciones - recibir mercadería | Bodeguero | GET /compras/1/recibir | permitido (no 403) | 500 | OK |
| PT-005 | R01 Recetas y médicos | Invitado | GET /recetas | redirige a login | redirige a login | OK |
| PT-010 | R02 Aprobar / rechazar receta | Invitado | POST /recetas/1/aprobar | redirige a login | redirige a login | OK |
| PT-015 | R03 Dashboard | Invitado | GET /dashboard | redirige a login | redirige a login | OK |
| PT-020 | R04 Perfil | Invitado | GET /profile | redirige a login | redirige a login | OK |
| PT-025 | R05 Productos | Invitado | GET /productos | redirige a login | redirige a login | OK |
| PT-030 | R06 Ventas | Invitado | GET /ventas | redirige a login | redirige a login | OK |
| PT-035 | R07 Usuarios | Invitado | GET /usuarios | 403 | 403 | OK |
| PT-040 | R08 Descuadres de stock controlado | Invitado | GET /descuadres-controlados | redirige a login | redirige a login | OK |
| PT-045 | R09 Convenios y coberturas | Invitado | GET /convenios | redirige a login | redirige a login | OK |
| PT-050 | R10 Lotes | Invitado | GET /lotes | 403 | 403 | OK |
| PT-055 | R11 Inventario - consulta | Invitado | GET /inventario | 403 | 403 | OK |
| PT-060 | R12 Próximos a vencer y consulta de canjes | Invitado | GET /inventario/proximos-vencer | 403 | 403 | OK |
| PT-065 | R13 Canjes - solicitar | Invitado | GET /inventario/canjes/solicitar/1 | 403 | 403 | OK |
| PT-070 | R14 Canjes - aprobar / rechazar | Invitado | POST /inventario/canjes/1/aprobar | 403 | 403 | OK |
| PT-075 | R15 Canjes - entregas, devoluciones y cierre | Invitado | POST /inventario/canjes/1/cerrar | 403 | 403 | OK |
| PT-080 | R16 Inventario - transferir, ajustar, marcar no apto | Invitado | GET /inventario/ajustar | 403 | 403 | OK |
| PT-085 | R17 Caja | Invitado | GET /caja | 403 | 403 | OK |
| PT-090 | R18 Pacientes | Invitado | GET /pacientes | 403 | 403 | OK |
| PT-095 | R19 Compras y recepciones - consulta | Invitado | GET /compras | redirige a login | redirige a login | OK |
| PT-100 | R20 Compras - crear orden | Invitado | GET /compras/crear | redirige a login | redirige a login | OK |
| PT-105 | R21 Proveedores | Invitado | GET /compras/proveedores | redirige a login | redirige a login | OK |
| PT-110 | R22 Recepciones - recibir mercadería | Invitado | GET /compras/1/recibir | redirige a login | redirige a login | OK |
