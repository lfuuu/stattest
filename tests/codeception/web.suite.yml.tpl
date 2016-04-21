class_name: _WebTester
modules:
    enabled:
        - REST
        - PhpBrowser
        - Asserts
    config:
        PhpBrowser:
            url: 'http://localhost:8080'
            host: 'localhost'
