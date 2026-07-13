# Seguridad de la Aplicación Web

## Objetivo

Este documento describe las pruebas de seguridad realizadas sobre la aplicación para validar que implementa controles adecuados frente a las vulnerabilidades más comunes del OWASP Top 10.

> **Nota:** Todas las pruebas deben realizarse únicamente en aplicaciones propias o para las cuales se cuente con autorización expresa.

---

# 1. Cross-Site Scripting (XSS)

## Objetivo

Verificar que la aplicación neutraliza la ejecución de código JavaScript enviado por un usuario.

## Áreas evaluadas

- Inicio de sesión
- Registro
- Perfil de usuario
- Formularios
- Comentarios
- Parámetros GET
- Parámetros POST

## Payloads de prueba

### Payload 1

```html
<script>alert('XSS')</script>
```

### Payload 2

```html
<img src=x onerror=alert('XSS')>
```

### Payload 3

```html
<svg onload=alert('XSS')>
```

### Payload 4

```html
<b>Texto en negrita</b>
```

## Resultado esperado

- El contenido debe mostrarse como texto.
- No debe ejecutarse JavaScript.
- Debe existir sanitización de entradas.
- Debe existir escape de caracteres especiales.

---

# 2. SQL Injection (SQLi)

## Objetivo

Comprobar que la aplicación utiliza consultas parametrizadas y evita la manipulación de consultas SQL.

## Áreas evaluadas

- Inicio de sesión
- Registro
- Recuperación de contraseña
- Formularios de búsqueda
- Parámetros GET
- Parámetros POST
- API REST

## Payloads de prueba

### Payload 1

' OR 1=1 --'
```

### Payload 2

admin' #
```

### Payload 3

admin' /*

### Payload 4

' OR '1'='1


## Resultado esperado

- No deben mostrarse errores relacionados con la base de datos.
- La aplicación debe responder con mensajes genéricos.
- La autenticación no debe omitirse.
- Las consultas deben ejecutarse mediante consultas parametrizadas (Prepared Statements).
- No debe alterarse el funcionamiento de la aplicación.

---

# 3. Fuerza Bruta (Brute Force)

## Objetivo

Comprobar que la aplicación protege las cuentas de usuario frente a múltiples intentos consecutivos de autenticación.

## Áreas evaluadas

- Inicio de sesión
- API de autenticación
- Recuperación de contraseña

## Casos de prueba

Realizar varios intentos consecutivos utilizando una cuenta de prueba con credenciales incorrectas.

Ejemplo:

```
Usuario: usuario_prueba
Contraseña: incorrecta1

Usuario: usuario_prueba
Contraseña: incorrecta2

Usuario: usuario_prueba
Contraseña: incorrecta3

Usuario: usuario_prueba
Contraseña: incorrecta4

Usuario: usuario_prueba
Contraseña: incorrecta5
```

## Resultado esperado

- Se debe limitar la cantidad de intentos de inicio de sesión.
- Debe existir un retraso entre intentos o un bloqueo temporal de la cuenta.
- El sistema debe registrar los intentos fallidos.
- Si la aplicación lo implementa, debe mostrarse un CAPTCHA o solicitar autenticación multifactor (MFA).

---

# 4. Gestión de Sesiones

## Objetivo

Verificar que las sesiones de usuario se administran de forma segura.

## Casos de prueba

- Iniciar sesión correctamente.
- Acceder a páginas protegidas.
- Cerrar sesión.
- Intentar volver utilizando el botón "Atrás" del navegador.
- Intentar acceder nuevamente a una URL protegida.
- Abrir la aplicación en otra pestaña del navegador.

## Validaciones

- La cookie debe tener el atributo **HttpOnly**.
- La cookie debe tener el atributo **Secure** cuando se utilice HTTPS.
- Debe utilizar el atributo **SameSite**.
- El identificador de sesión debe regenerarse después del inicio de sesión.
- La sesión debe invalidarse al cerrar sesión.
- Debe existir expiración automática por inactividad.

## Resultado esperado

- No debe reutilizarse una sesión cerrada.
- No debe permitirse el acceso a páginas protegidas después del cierre de sesión.
- Las cookies deben estar protegidas correctamente.
- La sesión debe expirar según la configuración establecida.


# Registro de Hallazgos

| Vulnerabilidad | Estado | Observaciones |
|----------------|--------|---------------|
| Cross-Site Scripting (XSS) | ☐ Pendiente | |
| SQL Injection (SQLi) | ☐ Pendiente | |
| Fuerza Bruta | ☐ Pendiente | |
| Gestión de Sesiones | ☐ Pendiente | |

---

# Conclusión

La aplicación se considerará segura únicamente cuando todas las pruebas anteriores se completen satisfactoriamente y no se detecten vulnerabilidades críticas, altas o medias relacionadas con XSS, SQL Injection, Fuerza Bruta o Gestión de Sesiones.
