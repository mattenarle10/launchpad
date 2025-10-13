/**
 * Reusable Data Table Component
 * Used across CDC and PC portals for displaying data tables
 */

class DataTable {
    constructor(config) {
        this.config = {
            containerId: config.containerId,
            columns: config.columns || [],
            actions: config.actions || [],
            data: config.data || [],
            pagination: config.pagination !== false,
            pageSize: config.pageSize || 10,
            searchable: config.searchable !== false,
            sortable: config.sortable !== false,
            onRowClick: config.onRowClick || null,
            emptyMessage: config.emptyMessage || 'No data available',
            loadingMessage: config.loadingMessage || 'Loading...'
        };

        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.searchTerm = '';
        this.filteredData = [];

        this.container = document.getElementById(this.config.containerId);
        if (!this.container) {
            console.error(`Container with id "${this.config.containerId}" not found`);
            return;
        }

        this.init();
    }

    init() {
        this.render();
    }

    setData(data) {
        this.config.data = data;
        this.filteredData = [...data];
        this.currentPage = 1;
        this.render();
    }

    render() {
        this.filteredData = this.filterData();
        this.filteredData = this.sortData(this.filteredData);

        const totalPages = Math.ceil(this.filteredData.length / this.config.pageSize);
        const startIndex = (this.currentPage - 1) * this.config.pageSize;
        const endIndex = startIndex + this.config.pageSize;
        const paginatedData = this.filteredData.slice(startIndex, endIndex);

        let html = '';

        // Empty state
        if (this.config.data.length === 0) {
            html = this.renderEmpty();
        } else if (this.filteredData.length === 0) {
            html = this.renderNoResults();
        } else {
            html = this.renderTable(paginatedData);
            
            if (this.config.pagination && totalPages > 1) {
                html += this.renderPagination(totalPages);
            }
        }

        this.container.innerHTML = html;
        this.attachEventListeners();
    }

    renderTable(data) {
        let html = '<table class="data-table">';
        
        // Header
        html += '<thead><tr>';
        this.config.columns.forEach(column => {
            const sortable = this.config.sortable && column.sortable !== false;
            const sortIcon = this.getSortIcon(column.key);
            html += `
                <th ${sortable ? `class="sortable" data-sort="${column.key}"` : ''}>
                    ${column.label} ${sortIcon}
                </th>
            `;
        });
        if (this.config.actions.length > 0) {
            html += '<th>Actions</th>';
        }
        html += '</tr></thead>';

        // Body
        html += '<tbody>';
        data.forEach((row, index) => {
            html += '<tr>';
            this.config.columns.forEach(column => {
                const value = this.getNestedValue(row, column.key);
                const formattedValue = column.format ? column.format(value, row) : value;
                html += `<td>${formattedValue}</td>`;
            });
            
            if (this.config.actions.length > 0) {
                html += '<td><div class="action-buttons">';
                this.config.actions.forEach(action => {
                    html += `
                        <button class="btn-action btn-${action.type || 'view'}" 
                                data-action="${action.type}" 
                                data-index="${(this.currentPage - 1) * this.config.pageSize + index}">
                            ${action.label}
                        </button>
                    `;
                });
                html += '</div></td>';
            }
            
            html += '</tr>';
        });
        html += '</tbody>';
        html += '</table>';

        return html;
    }

    renderPagination(totalPages) {
        const startItem = (this.currentPage - 1) * this.config.pageSize + 1;
        const endItem = Math.min(this.currentPage * this.config.pageSize, this.filteredData.length);
        
        let html = '<div class="pagination">';
        
        // Previous button
        html += `
            <button class="pagination-btn" 
                    data-page="prev" 
                    ${this.currentPage === 1 ? 'disabled' : ''}>
                Previous
            </button>
        `;

        // Page info
        html += `
            <span class="pagination-info">
                ${startItem}-${endItem} of ${this.filteredData.length}
            </span>
        `;

        // Next button
        html += `
            <button class="pagination-btn" 
                    data-page="next" 
                    ${this.currentPage === totalPages ? 'disabled' : ''}>
                Next
            </button>
        `;

        html += '</div>';
        return html;
    }

    renderEmpty() {
        return `
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-text">${this.config.emptyMessage}</div>
            </div>
        `;
    }

    renderNoResults() {
        return `
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <div class="empty-state-text">No results found</div>
                <div class="empty-state-subtext">Try adjusting your search or filter criteria</div>
            </div>
        `;
    }

    getSortIcon(columnKey) {
        if (!this.config.sortable) return '';
        if (this.sortColumn !== columnKey) return '↕️';
        return this.sortDirection === 'asc' ? '↑' : '↓';
    }

    filterData() {
        if (!this.searchTerm) return [...this.config.data];

        const term = this.searchTerm.toLowerCase();
        return this.config.data.filter(row => {
            return this.config.columns.some(column => {
                const value = this.getNestedValue(row, column.key);
                return String(value).toLowerCase().includes(term);
            });
        });
    }

    sortData(data) {
        if (!this.sortColumn) return data;

        return [...data].sort((a, b) => {
            const aVal = this.getNestedValue(a, this.sortColumn);
            const bVal = this.getNestedValue(b, this.sortColumn);

            let comparison = 0;
            if (aVal > bVal) comparison = 1;
            if (aVal < bVal) comparison = -1;

            return this.sortDirection === 'asc' ? comparison : -comparison;
        });
    }

    getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => current?.[key], obj);
    }

    search(term) {
        this.searchTerm = term;
        this.currentPage = 1;
        this.render();
    }

    sort(columnKey) {
        if (this.sortColumn === columnKey) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = columnKey;
            this.sortDirection = 'asc';
        }
        this.render();
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.filteredData.length / this.config.pageSize);
        
        if (page === 'prev' && this.currentPage > 1) {
            this.currentPage--;
        } else if (page === 'next' && this.currentPage < totalPages) {
            this.currentPage++;
        } else if (typeof page === 'number' && page >= 1 && page <= totalPages) {
            this.currentPage = page;
        }
        
        this.render();
    }

    attachEventListeners() {
        // Sort listeners
        const sortHeaders = this.container.querySelectorAll('th.sortable');
        sortHeaders.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sort(header.dataset.sort);
            });
        });

        // Action button listeners
        const actionButtons = this.container.querySelectorAll('[data-action]');
        actionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const action = button.dataset.action;
                const index = parseInt(button.dataset.index);
                const row = this.filteredData[index];
                
                const actionConfig = this.config.actions.find(a => a.type === action);
                if (actionConfig && actionConfig.onClick) {
                    actionConfig.onClick(row, index);
                }
            });
        });

        // Pagination listeners
        const paginationButtons = this.container.querySelectorAll('[data-page]');
        paginationButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.goToPage(button.dataset.page);
            });
        });
    }

    showLoading() {
        this.container.innerHTML = `
            <div class="loading">
                <div class="loading-spinner"></div>
                <p>${this.config.loadingMessage}</p>
            </div>
        `;
    }

    refresh() {
        this.render();
    }
}

// Export for use in other modules
export default DataTable;

