/**
 * Simple SPA Router
 */

class Router {
    constructor() {
        this.routes = {};
        this.currentRoute = null;
        
        // Listen for hash changes
        window.addEventListener('hashchange', () => this.handleRoute());
        window.addEventListener('load', () => this.handleRoute());
    }

    /**
     * Register a route
     */
    register(path, component) {
        this.routes[path] = component;
    }

    /**
     * Navigate to a route
     */
    navigate(path) {
        window.location.hash = path;
    }

    /**
     * Handle route change
     */
    async handleRoute() {
        const hash = window.location.hash.slice(1) || '/';
        const route = this.matchRoute(hash);
        
        if (route) {
            this.currentRoute = hash;
            const app = document.getElementById('app');
            app.innerHTML = '<div class="loading-spinner"></div>';
            
            try {
                const content = await route.component();
                app.innerHTML = content;
                
                // Call afterRender if it exists
                if (route.afterRender) {
                    route.afterRender();
                }
            } catch (error) {
                console.error('Route error:', error);
                app.innerHTML = '<div class="alert alert-error">Failed to load page</div>';
            }
        }
    }

    /**
     * Match route pattern
     */
    matchRoute(hash) {
        // Exact match
        if (this.routes[hash]) {
            return this.routes[hash];
        }

        // Pattern match (e.g. /students/:id)
        for (const path in this.routes) {
            const pattern = new RegExp('^' + path.replace(/:\w+/g, '(\\d+)') + '$');
            const match = hash.match(pattern);
            
            if (match) {
                const params = this.extractParams(path, hash);
                return {
                    ...this.routes[path],
                    params
                };
            }
        }

        // 404
        return this.routes['/404'] || {
            component: () => '<div class="alert alert-error">Page not found</div>'
        };
    }

    /**
     * Extract route parameters
     */
    extractParams(pattern, path) {
        const patternParts = pattern.split('/');
        const pathParts = path.split('/');
        const params = {};

        patternParts.forEach((part, index) => {
            if (part.startsWith(':')) {
                const key = part.slice(1);
                params[key] = pathParts[index];
            }
        });

        return params;
    }

    /**
     * Get current route
     */
    getCurrentRoute() {
        return this.currentRoute;
    }

    /**
     * Get route params
     */
    getParams() {
        const hash = window.location.hash.slice(1) || '/';
        const route = this.matchRoute(hash);
        return route?.params || {};
    }
}

export default new Router();

