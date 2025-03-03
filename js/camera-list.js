
const brand = ['nikon', 'canon'];
const type = ['dslr', 'action-camera'];

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
            daily: '',
            weekly: '',
            image: '',
            imageUrl: ''
        },
        openMediaUploader() {
            // Create the media frame.
            var frame = wp.media({
                title: 'dsd',
                button: { text: 'dsd' },
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
                brand: this.newCamera.brand,
                daily: this.newCamera.daily,
                weekly: this.newCamera.weekly,
                image: this.newCamera.image,
                imageUrl: this.newCamera.imageUrl
            };

            // Prepare the form data (including the nonce for security)
            const formData = new FormData();
            formData.append('action', 'create_camera_post'); // The action hook
            // formData.append('nonce', '<?php echo wp_create_nonce('create_camera_post_nonce'); ?>'); // The nonce value for security (replace with your actual nonce)
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
                brand: getRandomItem(brand),
                daily: 2934,
                weekly: 6789,
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