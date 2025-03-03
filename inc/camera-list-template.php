<?php

$brands = get_terms([
    'taxonomy' => 'brand',
    'hide_empty' => false,
]);

$types = get_terms([
    'taxonomy' => 'type',
    'hide_empty' => false,
]);

?>

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
                <!-- "Add Camera" Button -->
                <button @click="openAddCameraModal" class="button"><?php esc_html_e('Add Camera', 'camera-list'); ?></button>

                <table class="wp-list-table widefat striped">
                    <tbody>
                        <template x-for="item in items" :key="item.id">
                            <tr>
                                <td x-text="item.id"></td>
                                <td x-text="item.title"></td>
                                <td x-text="item.description"></td>
                                <td x-text="item.brand[0]?.name"></td>
                                <td x-text="item.type[0]?.name"></td>
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
        <style>
            .hidden {
                visibility: hidden
            }
        </style>
        <!-- Add Camera Modal -->
        <div
            :class="showAddCameraModal||'hidden'"
            style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
           background-color: rgba(0, 0, 0, 0.5); display: grid; place-items: center; 
           z-index: 9999; transition: opacity 0.3s ease-in-out;"
            x-cloak x-transition>


            <div class="modal-content" style="background: white; padding: 2em; border-radius: 8px; width:500px; position: relative;">

                <h2><?php esc_html_e('Add New Camera', 'camera-list'); ?></h2>
                <form @submit.prevent="submitAddCamera">
                    <div class="form-field">
                        <label for="camera-title"><?php esc_html_e('Camera', 'camera-list'); ?></label>
                        <input type="text" id="camera-title" x-model="newCamera.title" required />
                    </div>
                    <div class="form-field">
                        <label for="camera-description"><?php esc_html_e('Description', 'camera-list'); ?></label>
                        <input type="text" id="camera-description" x-model="newCamera.description" required />
                    </div>
                    <div class="form-field">
                        <label for="camera-brand"><?php esc_html_e('Brand', 'camera-list'); ?></label>
                        <select id="camera-brand" x-model="newCamera.brand" required>
                            <option value=""><?php esc_html_e('Select a brand', 'camera-list'); ?></option>
                            <?php foreach ($brands as $brand) : ?>
                                <option value="<?php echo esc_attr($brand->slug); ?>">
                                    <?php echo esc_html($brand->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="camera-type"><?php esc_html_e('Type', 'camera-list'); ?></label>
                        <select id="camera-type" x-model="newCamera.type" required>
                            <option value=""><?php esc_html_e('Select a type', 'camera-list'); ?></option>
                            <?php foreach ($types as $type) : ?>
                                <option value="<?php echo esc_attr($type->slug); ?>">
                                    <?php echo esc_html($type->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="camera-weekly"><?php esc_html_e('Daily Price', 'camera-list'); ?></label>
                        <input type="number" id="camera-daily" x-model="newCamera.daily" required />
                    </div>
                    <div class="form-field">
                        <label for="camera-weekly"><?php esc_html_e('Weekly Price', 'camera-list'); ?></label>
                        <input type="number" id="camera-weekly" x-model="newCamera.weekly" required />
                    </div>
                    <!-- New Image Field -->
                    <div class="form-field">
                        <label><?php esc_html_e('Image', 'camera-list'); ?></label>
                        <button type="button" @click="openMediaUploader" class="button">
                            <?php esc_html_e('Select Image', 'camera-list'); ?>
                        </button>
                        <template x-if="newCamera.imageUrl">
                            <img :src="newCamera.imageUrl" alt="<?php esc_attr_e('Selected Image', 'camera-list'); ?>" style="max-width: 100px; margin-top: 10px;" />
                        </template>
                    </div>
                    <div class="modal-footer" style="margin-top: 1em; display: flex; justify-content: space-between;">
                        <button type="button" @click="closeAddCameraModal" class="button"><?php esc_html_e('Cancel', 'camera-list'); ?></button>
                        <button type="submit" class="button button-primary"><?php esc_html_e('Add Camera', 'camera-list'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Include Alpine.js from CDN -->
<!-- <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script> -->
<!-- <script>
    const brand = ['nikon', 'canon'];
    const type = ['dslr', 'action-camera']

    function getRandomItem(arr) {
        const randomIndex = Math.floor(Math.random() * arr.length); // Generate a random index
        return arr[randomIndex]; // Return the item at that index
    }

    function tableData() {
        return {
            loading: true,
            showAddCameraModal: false,
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
            // Method to open the modal
            openAddCameraModal() {
                this.showAddCameraModal = true;
            },
            // Method to close the modal
            closeAddCameraModal() {
                this.showAddCameraModal = false;
            },
            newCamera: {
                title: '',
                description: '',
                brand: '',
                type: '',
                price: 0,
            },
            openMediaUploader() {
                // Create the media frame.
                var frame = wp.media({
                    title: 'Select or Upload Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false // Set to false to allow only one file to be selected
                });
                // When an image is selected in the media frame...
                frame.on('select', () => {
                    var attachment = frame.state().get('selection').first().toJSON();
                    // Save the attachment ID and URL to newCamera object
                    this.newCamera.image = attachment.id;
                    this.newCamera.imageUrl = attachment.url;
                });
                // Finally, open the modal on click
                frame.open();
            },
            // Method to submit new camera data
            submitAddCamera() {
                // create ajax request

                var cameraData = {
                    title: this.newCamera.title, // 'Incredible Soft Gloves',
                    description: this.newCamera.description, // 'Upgradable systematic flexibility' 
                    type: this.newCamera.type,
                    brand: this.newCamera.brand
                };

                // Prepare the form data (including the nonce for security)
                const formData = new FormData();
                formData.append('action', 'create_camera_post'); // The action hook
                formData.append('nonce', '<?php echo wp_create_nonce('create_camera_post_nonce'); ?>'); // The nonce value for security (replace with your actual nonce)
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


                // After adding, close the modal
                this.closeAddCameraModal();
                // Optionally, refresh the list of cameras
                this.loadTable();
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
                formData.append('nonce', '<?php echo wp_create_nonce('create_camera_post_nonce'); ?>'); // The nonce value for security (replace with your actual nonce)
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
</script> -->