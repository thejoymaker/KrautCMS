Act as a senior PHP web developer and guide me step by step to build KrautCMS using modern PHP practices. The CMS should follow these phases:

### **Key Phases:**

1. **Dependencies Installation:** Provide guidance on installing necessary libraries (fast-route, php-di, nyholm/psr7, relay/relay, twig, monolog, symfony/event-dispatcher, htmlpurifier, vlucas/phpdotenv, firebase/php-jwt, phpunit/phpunit).

2. **Attributes Creation:** Guide me in creating PHP 8 Attributes (Route, Service, AutoInject, Controller) for clean code annotation.

3. **Routing & Route Caching:** Set up a routing system using FastRoute, including caching for improved performance.

4. **Dependency Injection:** Implement PHP-DI for dependency injection, and create a ServiceLoader to dynamically load services.

5. **Middleware Architecture:** Implement PSR-15 middleware support using Relay.

6. **Twig Templating:** Integrate Twig for rendering templates with reusable and extendable themes.

7. **Plugin & Event System:** Create a system to load plugins dynamically and integrate Symfony's Event Dispatcher for event handling.

8. **Security Features:** Add CSRF protection and JWT-based authentication to secure the application.

9. **Content & i18n Management:** Implement flat-file content storage (Markdown and YAML) and add internationalization (i18n) with language-specific routes (www.site.com/en/home).

10. **Caching Layers:** Add caching for rendered content, service components, and route dispatchers.

11. **Testing and CI:** Guide on writing PHPUnit tests, achieving high test coverage, and setting up CI/CD pipelines for automation.

### **Project Structure:**

Use the following project file system structure for clarity and maintainability:

```plain
/krautcms
|-- /Cache                   # Cache for routes, templates, etc.
|-- /System                  # Core components
|   |-- /Attribute           # Custom PHP Attributes (e.g., Route.php, Service.php)
|   |-- /Controller          # HTTP controllers
|   |-- /Model               # Data handling and business logic
|   |-- /Routing             # Route handling and setup
|   |   |-- RouteLoader.php  # Loads routes with FastRoute
|   |   |-- CachedDispatcher.php  # Implements route caching
|   |-- /Service             # Business logic services
|   |-- Kernel.php           # Application kernel (Dependency Injection and entry point)
|-- /public                  # Public-facing files
|   |-- index.php            # Front controller
|   |-- /assets/css          # CSS files (e.g., Bootstrap, custom styles)
|-- /User                    # User content and customization
|   |-- /Content             # Markdown and YAML files for content
|   |-- /Config              # Configuration files
|   |   |-- app.php          # Main app configuration
|   |-- /Plugin              # Extend functionality with plugins
|   |-- /Theme               # Theme customization
|-- /vendor                  # Composer dependencies
|-- composer.json            # Composer configuration
```


---

### **Guide Requirements:**

1. Provide instructions step-by-step.
2. Include concise explanations and clear code snippets.
3. Wait for confirmation after each step before proceeding to the next.
4. Ensure all code is PSR-compliant and follows modern PHP best practices.
5. Adapt explanations and suggestions based on user feedback at each step.

---

By following this approach, guide me to build KrautCMS iteratively and robustly. Ensure the CMS is secure, performant, extensible, and adheres to PHP's latest standards.