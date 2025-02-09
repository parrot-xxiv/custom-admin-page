    <div class="wrap">
        <h1><?php esc_html_e('Headless JSON Table with Alpine.js', 'headless-json-table'); ?></h1>
    </div>
    <div x-data="tableData()" x-init="loadTable()">
        <!-- Search Controls -->
        <div class="search-controls" style="margin-bottom: 1em;">
            <input type="text" x-model="search" placeholder="<?php esc_attr_e('Search...', 'headless-json-table'); ?>" @keyup.enter="searchTable()" />
            <button @click="searchTable()" class="button"><?php esc_html_e('Search', 'headless-json-table'); ?></button>
        </div>
        <template x-if="loading">
            <p><?php esc_html_e('Loading data…', 'headless-json-table'); ?></p>
        </template>
        <template x-if="!loading">
            <div>
                <table class="wp-list-table widefat striped">
                    <tbody>
                        <template x-for="item in items" :key="item.id">
                            <tr>
                                <td x-text="item.id"></td>
                                <td x-text="item.name"></td>
                                <td x-text="item.email"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <!-- Pagination Controls -->
                <div class="pagination-controls" style="margin-top: 1em;">
                    <button class="button" :disabled="currentPage == 1" @click="goToFirstPage()">
                        <?php esc_html_e('First', 'headless-json-table'); ?>
                    </button>
                    <button class="button" :disabled="currentPage <= 1" @click="goToPage(currentPage - 1)">
                        <?php esc_html_e('Previous', 'headless-json-table'); ?>
                    </button>
                    <!-- Numeric Page Buttons -->
                    <div class="page-numbers" style="display: inline-block; margin: 0 10px;">
                        <template x-for="page in pages()" :key="page">
                            <button type="button"
                                @click="goToPage(page)"
                                :class="{'button-primary': page === currentPage, 'button': page !== currentPage}"
                                style="margin: 0 2px;">
                                <span x-text="page"></span>
                            </button>
                        </template>
                    </div>
                    <button class="button" :disabled="currentPage >= totalPages" @click="goToPage(currentPage + 1)">
                        <?php esc_html_e('Next', 'headless-json-table'); ?>
                    </button>
                    <button class="button" :disabled="currentPage == totalPages" @click="goToLastPage()">
                        <?php esc_html_e('Last', 'headless-json-table'); ?>
                    </button>
                    <!-- Additional Pagination Info -->
                    <div style="margin-top: 0.5em;">
                        <span x-text="`Page ${currentPage} of ${totalPages} | Total Items: ${totalItems}`"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>



    <!-- Include Alpine.js from CDN -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        function tableData() {
            return {
                loading: true,
                items: [],
                search: '',
                currentPage: 1,
                totalPages: 1,
                totalItems: 0,
                loadTable() {
                    this.loading = true;
                    let params = new URLSearchParams();
                    params.append('action', 'hjt_get_table_data');
                    params.append('s', this.search);
                    params.append('paged', this.currentPage);
                    fetch(ajaxurl + '?' + params.toString())
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                let result = data.data;
                                this.items = result.items;
                                this.totalPages = result.total_pages;
                                this.totalItems = result.total_items;
                                this.currentPage = result.current_page;
                            } else {
                                this.items = [];
                                this.totalPages = 1;
                                this.currentPage = 1;
                                this.totalItems = 0;
                            }
                            this.loading = false;
                        })
                        .catch(() => {
                            this.items = [];
                            this.loading = false;
                        });
                },
                searchTable() {
                    this.currentPage = 1;
                    this.loadTable();
                },
                goToPage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                        this.loadTable();
                    }
                },
                goToFirstPage() {
                    this.goToPage(1);
                },
                goToLastPage() {
                    this.goToPage(this.totalPages);
                },
                // New helper method: returns an array of page numbers
                pages() {
                    return Array.from({
                        length: this.totalPages
                    }, (v, i) => i + 1);
                }
            }
        }
    </script>