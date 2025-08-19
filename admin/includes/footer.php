            </div><!-- .admin-container -->
        </main><!-- .admin-main -->
    </div><!-- .admin-page -->
    
    <!-- Admin JavaScript -->
    <script>
        // Initialize Chart.js charts
        document.addEventListener('DOMContentLoaded', function() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart');
            if (salesCtx) {
                new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Sales',
                            data: [12, 19, 3, 5, 2, 3],
                            borderColor: '#007AFF',
                            backgroundColor: 'rgba(0, 122, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
            
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Electronics', 'Accessories', 'Smartphones', 'Laptops'],
                        datasets: [{
                            data: [300, 150, 200, 250],
                            backgroundColor: [
                                '#007AFF',
                                '#FF9500',
                                '#34C759',
                                '#AF52DE'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }
            
            // Period selector functionality
            const periodSelect = document.querySelector('.chart-period');
            if (periodSelect) {
                periodSelect.addEventListener('change', function() {
                    // Here you would typically make an AJAX call to update the charts
                    console.log('Period changed to:', this.value);
                });
            }
        });
        
        // Table row actions
        function viewOrder(orderId) {
            window.location.href = 'orders.php?action=view&id=' + orderId;
        }
        
        function editProduct(productId) {
            window.location.href = 'products.php?action=edit&id=' + productId;
        }
        
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                // Here you would make an AJAX call to delete the product
                console.log('Deleting product:', productId);
            }
        }
        
        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Search functionality
        function setupSearch() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    if (query.length > 2) {
                        // Here you would make an AJAX call to search
                        console.log('Searching for:', query);
                    }
                });
            }
        }
        
        // Initialize search
        setupSearch();
    </script>
</body>
</html>