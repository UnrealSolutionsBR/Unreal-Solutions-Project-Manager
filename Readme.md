<a id="readme-top"></a>

<!-- PROJECT LOGO -->

<div align="center">
  <img src="https://unrealsolutions.com.br/wp-content/uploads/2023/07/unreal-solutions-white-logotype.svg" alt="Logo" width="400" height="400">
  <h3 align="center">Unreal Solutions Project Manager</h3>
  <p align="center">
    Plugin modular para WordPress que permite a tus clientes gestionar proyectos, facturas, tickets de soporte y más desde un dashboard personalizado.
    <br />
    <a href="https://unrealsolutions.com.br/"><strong>Visitar sitio oficial »</strong></a>
    <br />
    <br />
    <a href="#usage">Ver ejemplo</a>
    ·
    <a href="#issues">Reportar bug</a>
    ·
    <a href="#roadmap">Solicitar funcionalidad</a>
  </p>
</div>

---

## Tabla de contenidos

* [Sobre el proyecto](#sobre-el-proyecto)
* [Tecnologías utilizadas](#tecnologías-utilizadas)
* [Instalación](#instalación)
* [Uso](#uso)
* [Roadmap](#roadmap)
* [Contribuciones](#contribuciones)
* [Licencia](#licencia)
* [Contacto](#contacto)

---

## Sobre el proyecto

Unreal Solutions Project Manager es un plugin hecho a medida para agencias creativas que gestionan servicios digitales y desean brindar a sus clientes un entorno profesional desde el cual puedan:

* Consultar el estado de sus proyectos
* Ver facturas y realizar pagos
* Enviar tickets de soporte
* Recibir notificaciones personalizadas

Se integra completamente en WordPress y aprovecha `Custom Post Types`, `shortcodes`, y `meta fields` para funcionar de forma modular y escalable.

<p align="right">(<a href="#readme-top">volver arriba</a>)</p>

## Tecnologías utilizadas

* PHP
* WordPress (CPT, hooks, WP\_Query)
* HTML/CSS (Tailwind-like custom styles)
* JavaScript (Vanilla JS)

<p align="right">(<a href="#readme-top">volver arriba</a>)</p>

## Instalación

1. Clona este repositorio dentro del directorio `wp-content/plugins`:

   ```bash
   git clone https://github.com/UnrealSolutionsBR/unreal-project-manager.git
   ```
2. Activa el plugin desde el panel de WordPress.
3. Asegúrate de tener clientes registrados con el rol `customer`.

<p align="right">(<a href="#readme-top">volver arriba</a>)</p>

## Uso

* Usa el shortcode `[upm_dashboard]` para mostrar el panel principal del cliente.
* Usa el shortcode `[upm_projects]` para mostrar la vista de proyectos con filtros visuales.
* Cada módulo (proyectos, facturas, tickets, hitos, notificaciones) es independiente y personalizable.

Ejemplo de integración en una página:

```php
[upm_dashboard]
```

<p align="right">(<a href="#readme-top">volver arriba</a>)</p>

## Roadmap

* [x] Sistema de proyectos con estado y progreso
* [x] Facturas automáticas por proyecto
* [x] Módulo de soporte con tickets
* [x] Hitos y recordatorios
* [x] Notificaciones internas
* [ ] Dashboard del admin para control general
* [ ] API REST para integración externa

<p align="right">(<a href="#readme-top">volver arriba</a>)</p>

## Contribuciones

1. Realiza un fork
2. Crea una rama con tu funcionalidad
3. Abre un Pull Request y describe el cambio

<p align="right">(<a href="#readme-top">volver arriba</a>)</p>

## Licencia

Este proyecto está licenciado bajo la licencia MIT. Consulta `LICENSE.txt` para más información.

<p align="right">(<a href="#readme-top">volver arriba</a>)</p>

## Contacto

Unreal Solutions - [@unrealsolutionsbr](https://www.instagram.com/unrealsolutionsbr) <br/>
[https://unrealsolutions.com.br](https://unrealsolutions.com.br)

<p align="right">(<a href="#readme-top">volver arriba</a>)</p>
