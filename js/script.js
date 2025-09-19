/**
 * Modern JavaScript for Accessible PDF Brochure
 * Enhanced with accessibility and modern features
 */
(function() {
    'use strict';
    
    // Check if browser supports modern features
    const supportsIntersectionObserver = 'IntersectionObserver' in window;
    const supportsSmoothScroll = 'scrollBehavior' in document.documentElement.style;
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initBrochureTab();
        initFileUpload();
        initAccessibilityFeatures();
        initPerformanceOptimizations();
    });
    
    /**
     * Initialize brochure tab functionality
     */
    function initBrochureTab() {
        const brochureTabLink = document.querySelector('a[href="#tab-pdf_brochure"]');
        const brochureContent = document.getElementById('tab-pdf_brochure');
        
        if (brochureTabLink && brochureContent) {
            brochureTabLink.addEventListener('click', function(e) {
                // Prevent default if smooth scroll is not supported
                if (!supportsSmoothScroll) {
                    e.preventDefault();
                }
                
                // Add loading state
                brochureTabLink.setAttribute('aria-busy', 'true');
                
                // Scroll to content with fallback
                setTimeout(() => {
                    if (supportsSmoothScroll) {
                        brochureContent.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                    } else {
                        brochureContent.scrollIntoView();
                    }
                    
                    // Remove loading state
                    brochureTabLink.removeAttribute('aria-busy');
                    
                    // Focus on content for accessibility
                    brochureContent.focus();
                }, 100);
            });
        }
    }
    
    /**
     * Initialize file upload functionality
     */
    function initFileUpload() {
        const fileInput = document.querySelector('input[name="product_brochure_file"]');
        const urlInput = document.querySelector('input[name="product_brochure"]');
        
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    if (file.type !== 'application/pdf') {
                        alert('Please select a PDF file.');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file size (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        alert('File size must be less than 10MB.');
                        this.value = '';
                        return;
                    }
                    
                    // Show file preview
                    showFilePreview(file);
                    
                    // Clear URL input when file is selected
                    if (urlInput) {
                        urlInput.value = '';
                    }
                }
            });
        }
        
        if (urlInput) {
            urlInput.addEventListener('input', function(e) {
                // Clear file input when URL is entered
                if (fileInput && e.target.value) {
                    fileInput.value = '';
                    hideFilePreview();
                }
            });
        }
    }
    
    /**
     * Show file preview
     */
    function showFilePreview(file) {
        let preview = document.querySelector('.pdf-upload-preview');
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'pdf-upload-preview';
            preview.innerHTML = `
                <div class="file-info">
                    <div class="file-icon">PDF</div>
                    <div class="file-details">
                        <div class="file-name"></div>
                        <div class="file-size"></div>
                    </div>
                    <button type="button" class="remove-file">Remove</button>
                </div>
            `;
            
            const fileInput = document.querySelector('input[name="product_brochure_file"]');
            if (fileInput) {
                fileInput.parentNode.appendChild(preview);
            }
        }
        
        // Update preview content
        preview.querySelector('.file-name').textContent = file.name;
        preview.querySelector('.file-size').textContent = formatFileSize(file.size);
        preview.classList.add('show');
        
        // Add remove functionality
        const removeBtn = preview.querySelector('.remove-file');
        removeBtn.addEventListener('click', function() {
            const fileInput = document.querySelector('input[name="product_brochure_file"]');
            if (fileInput) {
                fileInput.value = '';
            }
            hideFilePreview();
        });
    }
    
    /**
     * Hide file preview
     */
    function hideFilePreview() {
        const preview = document.querySelector('.pdf-upload-preview');
        if (preview) {
            preview.classList.remove('show');
        }
    }
    
    /**
     * Format file size
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /**
     * Initialize accessibility features
     */
    function initAccessibilityFeatures() {
        const brochureContainer = document.querySelector('.pdf-brochure-container');
        const brochureButton = document.querySelector('.pdf-brochure-container .button');
        
        if (brochureContainer) {
            // Add ARIA attributes
            brochureContainer.setAttribute('role', 'region');
            brochureContainer.setAttribute('aria-label', 'Product Brochure');
            
            // Add keyboard navigation support
            brochureContainer.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    brochureButton?.click();
                }
            });
        }
        
        if (brochureButton) {
            // Add focus management
            brochureButton.addEventListener('focus', function() {
                this.style.outline = '2px solid #007cba';
                this.style.outlineOffset = '2px';
            });
            
            brochureButton.addEventListener('blur', function() {
                this.style.outline = 'none';
            });
        }
    }
    
    /**
     * Initialize performance optimizations
     */
    function initPerformanceOptimizations() {
        // Lazy load PDF preview if needed
        if (supportsIntersectionObserver) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Load PDF preview or additional content here
                        entry.target.classList.add('loaded');
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            const brochureContainer = document.querySelector('.pdf-brochure-container');
            if (brochureContainer) {
                observer.observe(brochureContainer);
            }
        }
        
        // Add performance monitoring
        if ('performance' in window) {
            window.addEventListener('load', function() {
                const loadTime = performance.now();
                console.log(`PDF Brochure loaded in ${loadTime.toFixed(2)}ms`);
            });
        }
    }
    
    /**
     * Handle errors gracefully
     */
    window.addEventListener('error', function(e) {
        console.error('PDF Brochure Error:', e.error);
    });
    
    /**
     * Handle unhandled promise rejections
     */
    window.addEventListener('unhandledrejection', function(e) {
        console.error('PDF Brochure Promise Rejection:', e.reason);
    });
    
})();
