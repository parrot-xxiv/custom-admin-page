<div class="wrap">
    <h1><?php esc_html_e('Camera List', 'camera-list'); ?></h1>
    <div x-data="tableData()" x-init="loadTable()">
        <!-- Search Controls -->
        <div class="search-controls" style="margin-bottom: 1em;">
            <input type="text" x-model="search" placeholder="<?php esc_attr_e('Search...', 'headless-json-table'); ?>" @keyup.enter="searchTable()" />
            <button @click="searchTable()" class="button"><?php esc_html_e('Search', 'headless-json-table'); ?></button>
        </div>
        <template x-if="loading">
            <p><?php esc_html_e('Loading data…', 'camera-list'); ?></p>
        </template>
        <template x-if="!loading">
            <div>
                <table class="wp-list-table widefat striped">
                    <tbody>
                        <template x-for="item in items" :key="item.id">
                            <tr>
                                <td x-text="item.id"></td>
                                <td x-text="item.title"></td>
                                <td x-text="item.description"></td>
                                <td x-text="item.brand[0].name"></td>
                                <td x-text="item.type[0].name"></td>
                                <td x-text="item.price"></td>
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
                    <!-- Numeric Page Buttons (sliding window) -->
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
                <button @click="generateCameras">Generate Records</button>
                <button @click="deleteAllCameras">Delete All Cameras</button>
            </div>
        </template>
    </div>
</div>
<!-- Include Alpine.js from CDN -->
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    const brand = ['nikon', 'canon'];
    const type = ['dslr', 'action-camera']

    function getRandomItem(arr) {
        const randomIndex = Math.floor(Math.random() * arr.length); // Generate a random index
        return arr[randomIndex]; // Return the item at that index
    }

    function tableData() {
        return {
            loading: true,
            items: [],
            search: '',
            currentPage: 1,
            totalPages: 1,
            totalItems: 0,
            faker: null,
            async init() {
                const f = await import('https://esm.sh/@faker-js/faker');
                this.faker = await f.faker
            },
            loadTable() {
                this.loading = true;
                let params = new URLSearchParams();
                params.append('action', 'get_camera_list');
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
            // New pages() method to return a sliding window of up to 5 pages.
            pages() {
                const maxButtons = 5;
                let start = Math.max(1, this.currentPage - Math.floor(maxButtons / 2));
                let end = start + maxButtons - 1;

                if (end > this.totalPages) {
                    end = this.totalPages;
                    start = Math.max(1, end - maxButtons + 1);
                }

                let pages = [];
                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
                return pages;
            },
            // Delete all records
            deleteAllCameras() {
                if (confirm('Are you sure you want to delete all cameras?')) {
                    let params = new URLSearchParams();
                    params.append('action', 'delete_all_camera');

                    fetch(ajaxurl + '?' + params.toString(), {
                            method: 'POST'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // After successful deletion, reload the table
                                this.loadTable();
                            } else {
                                alert('Error deleting the cameras.');
                            }
                        })
                        .catch(() => {
                            alert('There was an error deleting the cameras.');
                        });
                }
            },
            generateCameras() {
                var cameraData = {
                    title: 'Camera ' + this.faker.commerce.productName(), // 'Incredible Soft Gloves',
                    description: this.faker.company.catchPhrase(), // 'Upgradable systematic flexibility' 
                    type: getRandomItem(type),
                    brand: getRandomItem(brand)
                };

                // Prepare the form data (including the nonce for security)
                const formData = new FormData();
                formData.append('action', 'create_camera_post'); // The action hook
                formData.append('nonce', cameraAjax.nonce); // The nonce value for security (replace with your actual nonce)
                formData.append('camera_data', JSON.stringify(cameraData)); // Add the camera data

                // Send the request using fetch
                fetch(ajaxurl, {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json()) // Parse the JSON response
                    .then(data => {
                        if (data.success) {
                            console.log('Post created successfully:', data.message);
                            this.loadTable();
                        } else {
                            console.log('Error creating post:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Request failed', error);
                    });

            },
        }
    }
</script>