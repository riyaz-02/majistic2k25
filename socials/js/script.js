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

    // Add Instagram post load more functionality
    const loadMoreBtn = document.getElementById('load-more');
    const hiddenPosts = document.querySelectorAll('.hidden-post');
    
    if (loadMoreBtn && hiddenPosts.length > 0) {
        loadMoreBtn.addEventListener('click', function() {
            let postsToShow = 0;
            const increment = 3; // Show 3 more posts at a time
            
            // Show next batch of hidden posts
            for (let i = 0; i < increment; i++) {
                if (hiddenPosts[i]) {
                    hiddenPosts[i].classList.remove('hidden-post');
                    
                    // Remove from NodeList by using the modified class
                    hiddenPosts[i].classList.add('shown-post');
                    postsToShow++;
                }
            }
            
            // Update the nodelist of hidden posts
            const remainingHiddenPosts = document.querySelectorAll('.hidden-post');
            
            // Hide button if no more posts
            if (remainingHiddenPosts.length === 0) {
                loadMoreBtn.style.display = 'none';
            }
            
            // Re-process embeds
            if (window.instgrm) {
                window.instgrm.Embeds.process();
            }
            
            if (window.FB) {
                window.FB.XFBML.parse();
            }
        });
    } else if (loadMoreBtn && hiddenPosts.length === 0) {
        // If there are no hidden posts, hide the load more button
        loadMoreBtn.style.display = 'none';
    }

    // Load more functionality for Instagram
    const loadMoreInstagram = document.getElementById('load-more-instagram');
    const hiddenInstagramPosts = document.querySelectorAll('#instagram .hidden-post');
    
    if (loadMoreInstagram) {
        loadMoreInstagram.addEventListener('click', function() {
            let postsToShow = 0;
            const increment = 3; // Show 3 more posts at a time
            
            // Show next batch of hidden posts
            for (let i = 0; i < increment; i++) {
                if (hiddenInstagramPosts[i]) {
                    hiddenInstagramPosts[i].classList.remove('hidden-post');
                    postsToShow++;
                }
            }
            
            // Hide button if no more posts
            if (postsToShow < increment) {
                loadMoreInstagram.style.display = 'none';
            }
            
            // Re-process Instagram embeds
            if (window.instgrm) {
                window.instgrm.Embeds.process();
            }
        });
    }
    
    // Load more functionality for Facebook
    const loadMoreFacebook = document.getElementById('load-more-facebook');
    const hiddenFacebookPosts = document.querySelectorAll('#facebook .hidden-post');
    
    if (loadMoreFacebook) {
        loadMoreFacebook.addEventListener('click', function() {
            let postsToShow = 0;
            const increment = 3; // Show 3 more posts at a time
            
            // Show next batch of hidden posts
            for (let i = 0; i < increment; i++) {
                if (hiddenFacebookPosts[i]) {
                    hiddenFacebookPosts[i].classList.remove('hidden-post');
                    postsToShow++;
                }
            }
            
            // Hide button if no more posts
            if (postsToShow < increment) {
                loadMoreFacebook.style.display = 'none';
            }
            
            // Re-parse Facebook embeds
            if (window.FB) {
                window.FB.XFBML.parse();
            }
        });
    }

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
});

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
        });
    }, 2000); // Give embeds time to load
    
    // Add a shimmer effect to the posts as they load
    const posts = document.querySelectorAll('.social-post');
    posts.forEach(post => {
        post.classList.add('post-loaded');
    });
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
                iframe.style.minHeight = '350px';
            }
        });
    }, 500);
});
