document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation Toggle
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    hamburger.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        
        // Animate hamburger to X
        const spans = hamburger.querySelectorAll('span');
        spans.forEach(span => span.classList.toggle('active'));
        
        if (navLinks.classList.contains('active')) {
            hamburger.querySelector('span:nth-child(1)').style.transform = 'rotate(45deg) translate(5px, 5px)';
            hamburger.querySelector('span:nth-child(2)').style.opacity = '0';
            hamburger.querySelector('span:nth-child(3)').style.transform = 'rotate(-45deg) translate(7px, -6px)';
        } else {
            hamburger.querySelector('span:nth-child(1)').style.transform = 'none';
            hamburger.querySelector('span:nth-child(2)').style.opacity = '1';
            hamburger.querySelector('span:nth-child(3)').style.transform = 'none';
        }
    });

    // Tab functionality for social media sections
    const tabButtons = document.querySelectorAll('.tab-btn');
    const socialSections = document.querySelectorAll('.social-feed-section');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            // Update active tab button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show the relevant section
            socialSections.forEach(section => {
                section.classList.remove('active');
                if (section.id === tab) {
                    section.classList.add('active');
                }
            });
            
            // Re-process embeds when tab is changed
            if (tab === 'instagram' && window.instgrm) {
                window.instgrm.Embeds.process();
            } else if (tab === 'facebook' && window.FB) {
                window.FB.XFBML.parse();
            }
        });
    });

    // Gallery Lazy Loading
    const galleryItems = document.querySelectorAll('.gallery-item img');
    
    if ('IntersectionObserver' in window) {
        const lazyImageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const lazyImage = entry.target;
                    if (lazyImage.dataset.src) {
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.remove('lazy');
                        lazyImageObserver.unobserve(lazyImage);
                    }
                }
            });
        });

        galleryItems.forEach(image => {
            if (image.dataset.src) {
                lazyImageObserver.observe(image);
            }
        });
    }

    // Add a slight delay when hovering over gallery items
    const items = document.querySelectorAll('.gallery-item');
    
    items.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

    // Smooth scroll for anchor links within the page
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Image loading animation
    const images = document.querySelectorAll('.gallery-item img, .fallback-item img');
    images.forEach(img => {
        if (img.complete) {
            img.classList.add('loaded');
        } else {
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });
        }
        
        // Add error handling for images
        img.addEventListener('error', function() {
            this.src = '../assets/images/placeholder.jpg'; // Fallback image
            this.alt = 'Image not available';
        });
    });

    // Image loading and error handling for Instagram posts
    const instaImages = document.querySelectorAll('.post-media img');
    instaImages.forEach(img => {
        // Show loading state
        img.closest('.post-media').classList.add('loading');
        
        // Once loaded, remove loading state
        img.onload = function() {
            img.closest('.post-media').classList.remove('loading');
            img.closest('.post-media').classList.add('loaded');
        }
        
        // Handle errors
        img.onerror = function() {
            img.closest('.post-media').classList.remove('loading');
            img.closest('.post-media').classList.add('error');
            img.src = '../assets/images/placeholder.jpg';
            img.alt = 'Image unavailable';
        }
    });

    // Simplified video playback function - no custom controls
    function initVideoPlayers(container = document) {
        const videos = container.querySelectorAll('video');
        
        videos.forEach(video => {
            // Enable controls on the video element
            video.setAttribute('controls', 'true');
            
            // Set click handler directly on video
            video.addEventListener('click', function(e) {
                // Let the native controls handle playback
                e.stopPropagation();
            });
            
            // Handle play/pause state for styling parent container
            video.addEventListener('play', function() {
                const instagramPost = this.closest('.instagram-post');
                if (instagramPost) {
                    instagramPost.classList.add('playing-video');
                }
            });
            
            video.addEventListener('pause', function() {
                const instagramPost = this.closest('.instagram-post');
                if (instagramPost) {
                    instagramPost.classList.remove('playing-video');
                }
            });
            
            video.addEventListener('ended', function() {
                const instagramPost = this.closest('.instagram-post');
                if (instagramPost) {
                    instagramPost.classList.remove('playing-video');
                }
            });
            
            // Log for debugging
            console.log('Native video player initialized');
        });
    }

    // Initialize video players
    setTimeout(initVideoPlayers, 500);
    
    // Add loading animation for images
    document.querySelectorAll('.post-media:not(.video)').forEach(media => {
        const img = media.querySelector('img');
        if (!img) return;
        
        // Add loading placeholder
        const placeholder = document.createElement('div');
        placeholder.className = 'loading-placeholder';
        placeholder.innerHTML = '<div class="loading-spinner"></div>';
        media.appendChild(placeholder);
        
        // Remove placeholder when image loads
        img.addEventListener('load', () => {
            placeholder.remove();
        });
        
        // Handle image load errors
        img.addEventListener('error', () => {
            placeholder.innerHTML = '<i class="fas fa-image" style="font-size: 24px; color: #ccc;"></i>';
        });
    });

    // Make Instagram embeds responsive
    window.addEventListener('resize', function() {
        if (window.instgrm) {
            window.instgrm.Embeds.process();
        }
        if (window.FB) {
            window.FB.XFBML.parse();
        }
    });

    // Add hover effects for Instagram posts
    const instagramPosts = document.querySelectorAll('.instagram-post');
    
    instagramPosts.forEach(post => {
        post.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        post.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

    // Add hover effects for social posts
    const socialPosts = document.querySelectorAll('.social-post');
    
    socialPosts.forEach(post => {
        post.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        post.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

    // Initialize layout adjustments
    adjustGridLayout();

    // Add clear cache button functionality
    const clearCacheLink = document.createElement('a');
    clearCacheLink.href = "?clear_cache=1";
    clearCacheLink.className = "clear-cache-link";
    clearCacheLink.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Feed';
    clearCacheLink.title = "Refresh Instagram feed";
    
    const sectionHeader = document.querySelector('.section-header');
    if (sectionHeader) {
        sectionHeader.appendChild(clearCacheLink);
    }
});

// Add this for a nice parallax-like scrolling effect
window.addEventListener('scroll', function() {
    if (window.innerWidth > 768) {  // Only use parallax on larger screens
        const scrolled = window.scrollY;
        const instagramPosts = document.querySelectorAll('.instagram-post:not(.playing-video)');
        
        instagramPosts.forEach((post, index) => {
            const direction = index % 2 === 0 ? 1 : -1;
            const speed = 0.03;
            
            if (isElementInViewport(post)) {
                requestAnimationFrame(() => {
                    post.style.transform = `translateY(${direction * scrolled * speed}px)`;
                });
            }
        });
    }
});

// Helper function to check if element is in viewport
function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.bottom >= 0
    );
}

// Instagram API Error Handling
window.onload = function() {
    const gallery = document.querySelector('.gallery');
    const noPostsDiv = document.querySelector('.no-posts');
    
    if (gallery && gallery.children.length === 0 && !noPostsDiv) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'no-posts';
        errorDiv.innerHTML = '<p>Unable to load Instagram posts at this time. Please try again later.</p>';
        gallery.appendChild(errorDiv);
    }
    
    // Make the active state for the navigation
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.site-navigation ul li a');
    
    navLinks.forEach(link => {
        if (currentPage.includes('/socials/')) {
            if (link.textContent.includes('SOCIAL')) {
                link.classList.add('active');
            }
        }
    });

    // Process Instagram embeds
    if (window.instgrm) {
        window.instgrm.Embeds.process();
    }
    
    // Process Facebook embeds
    if (window.FB) {
        window.FB.XFBML.parse();
    }
    
    // Fix iframe sizing issues
    setTimeout(function() {
        const iframes = document.querySelectorAll('.social-post iframe');
        iframes.forEach(iframe => {
            // Remove fixed height if present
            if (iframe.style.height) {
                iframe.style.height = 'auto';
                iframe.style.minHeight = '350px';
            }
            
            // For Facebook posts, ensure proper sizing
            if (iframe.closest('.facebook-post')) {
                iframe.style.minHeight = '500px';
            }
        });
        
        // Force container height to match content
        const posts = document.querySelectorAll('.social-post');
        posts.forEach(post => {
            const content = post.querySelector('iframe, blockquote, .api-post-link');
            if (content) {
                post.style.height = 'auto';
            }
        });
    }, 2000); // Give embeds time to load
    
    // Add post-loaded class to trigger animations
    const posts = document.querySelectorAll('.social-post');
    posts.forEach((post, index) => {
        setTimeout(() => {
            post.classList.add('post-loaded');
        }, index * 100);
    });

    // Add "loaded" class to the body for page transition effects
    document.body.classList.add('loaded');
    
    // Add scroll animations
    const instagramPosts = document.querySelectorAll('.instagram-post');
    instagramPosts.forEach((post, index) => {
        setTimeout(() => {
            post.classList.add('visible');
        }, 100 * index);
    });

    // Fix any issues with post heights - including both visible and hidden posts
    setTimeout(() => {
        document.querySelectorAll('.instagram-post').forEach(post => {
            const media = post.querySelector('.post-media');
            if (media && media.classList.contains('video')) {
                post.classList.add('video-post');
            }
        });
        
        // Make sure videos in visible posts are initialized
        initVideoPlayers();
    }, 1000);
};

// Add resize handler to maintain proper sizes
window.addEventListener('resize', function() {
    // Process embeds on resize
    if (window.instgrm) {
        window.instgrm.Embeds.process();
    }
    
    if (window.FB) {
        window.FB.XFBML.parse();
    }
    
    // Fix iframe sizing issues after resize
    setTimeout(function() {
        const iframes = document.querySelectorAll('.social-post iframe');
        iframes.forEach(iframe => {
            if (iframe.style.height) {
                iframe.style.height = 'auto';
                
                // Adjust min-height based on device width
                if (window.innerWidth < 480) {
                    iframe.style.minHeight = '350px';
                } else if (window.innerWidth < 768) {
                    iframe.style.minHeight = '380px';
                } else {
                    iframe.style.minHeight = '450px';
                }
                
                // For Facebook posts, ensure proper sizing
                if (iframe.closest('.facebook-post')) {
                    iframe.style.minHeight = window.innerWidth < 768 ? '400px' : '500px';
                }
            }
        });
    }, 500);
});

// Add mutation observer to fix heights when embeds load or change
if (window.MutationObserver) {
    const socialGrid = document.querySelector('.social-grid');
    if (socialGrid) {
        const observer = new MutationObserver(function(mutations) {
            // Process embeds again
            if (window.instgrm) {
                window.instgrm.Embeds.process();
            }
            
            if (window.FB) {
                window.FB.XFBML.parse();
            }
            
            // Fix container heights
            const posts = document.querySelectorAll('.social-post');
            posts.forEach(post => {
                post.style.height = 'auto';
            });
        });
        
        observer.observe(socialGrid, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }
}

// Modified function to handle masonry layout adjustments for larger posts
function adjustGridLayout() {
    const grid = document.querySelector('.instagram-grid');
    const posts = document.querySelectorAll('.instagram-post');
    
    if (!grid || posts.length === 0) return;
    
    // Let browser calculate natural heights for all posts
    setTimeout(() => {
        // For smaller posts, try to make them fit in a more compact way
        posts.forEach(post => {
            const height = post.offsetHeight;
            
            // Adjusted sizes for larger post containers
            if (height < 350) {
                post.style.gridRow = 'span 1';
            } else if (height < 550) {
                post.style.gridRow = 'span 2';
            } else {
                post.style.gridRow = 'span 3';
            }
        });
        
        // Apply staggered animations for better visual appearance
        posts.forEach((post, index) => {
            const delay = (index % 12) * 100; // Create groups of 12 for animation delays
            post.style.animationDelay = `${delay}ms`;
        });
    }, 100);

    // Additional responsive adjustments
    const windowWidth = window.innerWidth;
    
    if (windowWidth < 576) {
        // Optimize layout for mobile
        posts.forEach(post => {
            // On mobile, don't use variable heights
            post.style.gridRow = 'auto';
        });
    }
}

// Adjust grid layout when window is resized
window.addEventListener('resize', adjustGridLayout);

// Add layout adjustment after images load
window.addEventListener('load', function() {
    // Wait for all images to load properly
    setTimeout(adjustGridLayout, 500);
});

// Improve responsiveness based on screen size
function adjustResponsiveness() {
    const windowWidth = window.innerWidth;
    const instagramPosts = document.querySelectorAll('.instagram-post');
    
    // Set appropriate animation delay based on screen size
    instagramPosts.forEach((post, index) => {
        let delay;
        if (windowWidth < 576) {
            // Less delay for mobile (feels faster)
            delay = (index % 6) * 80;
        } else if (windowWidth < 992) {
            delay = (index % 8) * 90;
        } else {
            delay = (index % 12) * 100;
        }
        
        post.style.animationDelay = `${delay}ms`;
    });
    
    // Handle video display optimizations
    const videos = document.querySelectorAll('video');
    videos.forEach(video => {
        if (windowWidth < 576) {
            // Better mobile video settings
            video.setAttribute('preload', 'none'); // Save bandwidth on mobile
            video.setAttribute('playsinline', ''); // Ensure inline playback
            video.setAttribute('controls', 'true');
        }
    });
}

// Call on load and resize
window.addEventListener('load', adjustResponsiveness);
window.addEventListener('resize', adjustResponsiveness);
