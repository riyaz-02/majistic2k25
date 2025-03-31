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

    // Detect desktop mode on mobile and adjust layout
    function detectDesktopMode() {
        const screenWidth = window.screen.width;
        const viewportWidth = window.innerWidth;
        
        const isDesktopMode = (viewportWidth > screenWidth * 1.2) || 
                              (screenWidth < 900 && viewportWidth > 1000) ||
                              (window.visualViewport && window.visualViewport.scale > 1.2);
        
        if (isDesktopMode) {
            document.body.classList.add('desktop-mode');
            
            const instagramGrid = document.querySelector('.instagram-grid');
            if (instagramGrid) {
                if (screenWidth < 500) {
                    instagramGrid.style.gridTemplateColumns = '1fr';
                } else {
                    instagramGrid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(280px, 1fr))';
                }
            }
            
            document.querySelectorAll('.instagram-post').forEach(post => {
                post.setAttribute('style', 
                    'transform: none !important; ' +
                    'transition: none !important; ' +
                    'animation: none !important; ' + 
                    'position: relative !important; ' +
                    'z-index: 1 !important; ' + 
                    'margin-bottom: 20px !important;'
                );
                
                post.onmouseenter = null;
                post.onmouseleave = null;
            });
        } else {
            document.body.classList.remove('desktop-mode');
        }
    }
    
    detectDesktopMode();
    window.addEventListener('resize', detectDesktopMode);

    // Tab functionality for social media sections
    const tabButtons = document.querySelectorAll('.tab-btn');
    const socialSections = document.querySelectorAll('.social-feed-section');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            socialSections.forEach(section => {
                section.classList.remove('active');
                if (section.id === tab) {
                    section.classList.add('active');
                }
            });
            
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

    const items = document.querySelectorAll('.gallery-item');
    
    items.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

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

    const images = document.querySelectorAll('.gallery-item img, .fallback-item img');
    images.forEach(img => {
        if (img.complete) {
            img.classList.add('loaded');
        } else {
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });
        }
        
        img.addEventListener('error', function() {
            this.src = '../assets/images/placeholder.jpg';
            this.alt = 'Image not available';
        });
    });

    const instaImages = document.querySelectorAll('.post-media img');
    instaImages.forEach(img => {
        img.closest('.post-media').classList.add('loading');
        
        img.onload = function() {
            img.closest('.post-media').classList.remove('loading');
            img.closest('.post-media').classList.add('loaded');
        }
        
        img.onerror = function() {
            img.closest('.post-media').classList.remove('loading');
            img.closest('.post-media').classList.add('error');
            img.src = '../assets/images/placeholder.jpg';
            img.alt = 'Image unavailable';
        }
    });

    function initVideoPlayers(container = document) {
        const videos = container.querySelectorAll('video');
        
        videos.forEach(video => {
            video.setAttribute('controls', 'true');
            
            video.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
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
            
            console.log('Native video player initialized');
        });
    }

    setTimeout(initVideoPlayers, 500);
    
    document.querySelectorAll('.post-media:not(.video)').forEach(media => {
        const img = media.querySelector('img');
        if (!img) return;
        
        const placeholder = document.createElement('div');
        placeholder.className = 'loading-placeholder';
        placeholder.innerHTML = '<div class="loading-spinner"></div>';
        media.appendChild(placeholder);
        
        img.addEventListener('load', () => {
            placeholder.remove();
        });
        
        img.addEventListener('error', () => {
            placeholder.innerHTML = '<i class="fas fa-image" style="font-size: 24px; color: #ccc;"></i>';
        });
    });

    window.addEventListener('resize', function() {
        if (window.instgrm) {
            window.instgrm.Embeds.process();
        }
        if (window.FB) {
            window.FB.XFBML.parse();
        }
    });

    const instagramPosts = document.querySelectorAll('.instagram-post');
    
    instagramPosts.forEach(post => {
        post.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        post.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

    const socialPosts = document.querySelectorAll('.social-post');
    
    socialPosts.forEach(post => {
        post.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        post.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });

    adjustGridLayout();

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

window.addEventListener('scroll', function() {
    if (document.body.classList.contains('desktop-mode')) {
        document.querySelectorAll('.instagram-post').forEach(post => {
            post.setAttribute('style', 
                'transform: none !important; ' +
                'transition: none !important; ' +
                'animation: none !important; ' + 
                'position: relative !important; ' +
                'z-index: 1 !important;'
            );
        });
        return;
    }
    
    if (window.innerWidth > 768) {
        const scrolled = window.scrollY;
        const instagramPosts = document.querySelectorAll('.instagram-post:not(.playing-video)');
        
        instagramPosts.forEach((post, index) => {
            const direction = index % 2 === 0 ? 1 : -1;
            const speed = 0.03;
            
            if (isElementInViewport(post)) {
                post.style.transform = `translateY(${direction * scrolled * speed}px)`;
            }
        });
    }
    
    detectDesktopMode();
});

function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.bottom >= 0
    );
}

window.onload = function() {
    const gallery = document.querySelector('.gallery');
    const noPostsDiv = document.querySelector('.no-posts');
    
    if (gallery && gallery.children.length === 0 && !noPostsDiv) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'no-posts';
        errorDiv.innerHTML = '<p>Unable to load Instagram posts at this time. Please try again later.</p>';
        gallery.appendChild(errorDiv);
    }
    
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.site-navigation ul li a');
    
    navLinks.forEach(link => {
        if (currentPage.includes('/socials/')) {
            if (link.textContent.includes('SOCIAL')) {
                link.classList.add('active');
            }
        }
    });

    if (window.instgrm) {
        window.instgrm.Embeds.process();
    }
    
    if (window.FB) {
        window.FB.XFBML.parse();
    }
    
    setTimeout(function() {
        const iframes = document.querySelectorAll('.social-post iframe');
        iframes.forEach(iframe => {
            if (iframe.style.height) {
                iframe.style.height = 'auto';
                iframe.style.minHeight = '350px';
            }
            
            if (iframe.closest('.facebook-post')) {
                iframe.style.minHeight = '500px';
            }
        });
        
        const posts = document.querySelectorAll('.social-post');
        posts.forEach(post => {
            const content = post.querySelector('iframe, blockquote, .api-post-link');
            if (content) {
                post.style.height = 'auto';
            }
        });
    }, 2000);
    
    const posts = document.querySelectorAll('.social-post');
    posts.forEach((post, index) => {
        setTimeout(() => {
            post.classList.add('post-loaded');
        }, index * 100);
    });

    document.body.classList.add('loaded');
    
    const instagramPosts = document.querySelectorAll('.instagram-post');
    instagramPosts.forEach((post, index) => {
        setTimeout(() => {
            post.classList.add('visible');
        }, 100 * index);
    });

    setTimeout(() => {
        document.querySelectorAll('.instagram-post').forEach(post => {
            const media = post.querySelector('.post-media');
            if (media && media.classList.contains('video')) {
                post.classList.add('video-post');
            }
        });
        
        initVideoPlayers();
    }, 1000);
};

window.addEventListener('resize', function() {
    if (window.instgrm) {
        window.instgrm.Embeds.process();
    }
    
    if (window.FB) {
        window.FB.XFBML.parse();
    }
    
    setTimeout(function() {
        const iframes = document.querySelectorAll('.social-post iframe');
        iframes.forEach(iframe => {
            if (iframe.style.height) {
                iframe.style.height = 'auto';
                
                if (window.innerWidth < 480) {
                    iframe.style.minHeight = '350px';
                } else if (window.innerWidth < 768) {
                    iframe.style.minHeight = '380px';
                } else {
                    iframe.style.minHeight = '450px';
                }
                
                if (iframe.closest('.facebook-post')) {
                    iframe.style.minHeight = window.innerWidth < 768 ? '400px' : '500px';
                }
            }
        });
    }, 500);
});

if (window.MutationObserver) {
    const socialGrid = document.querySelector('.social-grid');
    if (socialGrid) {
        const observer = new MutationObserver(function(mutations) {
            if (window.instgrm) {
                window.instgrm.Embeds.process();
            }
            
            if (window.FB) {
                window.FB.XFBML.parse();
            }
            
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

function adjustGridLayout() {
    const grid = document.querySelector('.instagram-grid');
    const posts = document.querySelectorAll('.instagram-post');
    
    if (!grid || posts.length === 0) return;
    
    if (document.body.classList.contains('desktop-mode')) {
        posts.forEach(post => {
            post.setAttribute('style', 
                'transform: none !important; ' +
                'transition: none !important; ' +
                'animation: none !important; ' + 
                'position: relative !important; ' +
                'z-index: 1 !important; ' +
                'min-width: 280px !important; ' +
                'width: 100% !important; ' +
                'max-width: 100% !important; ' +
                'height: 350px !important; ' +
                'margin-bottom: 20px !important; ' +
                'grid-row: auto !important;'
            );
        });
        
        return;
    }
    
    setTimeout(() => {
        posts.forEach(post => {
            const height = post.offsetHeight;
            
            if (height < 350) {
                post.style.gridRow = 'span 1';
            } else if (height < 550) {
                post.style.gridRow = 'span 2';
            } else {
                post.style.gridRow = 'span 3';
            }
        });
        
        posts.forEach((post, index) => {
            const delay = (index % 12) * 100;
            post.style.animationDelay = `${delay}ms`;
        });
    }, 100);

    const windowWidth = window.innerWidth;
    
    if (windowWidth < 576) {
        posts.forEach(post => {
            post.style.gridRow = 'auto';
        });
    }
}

window.addEventListener('resize', adjustGridLayout);

window.addEventListener('load', function() {
    setTimeout(adjustGridLayout, 500);
});

function adjustResponsiveness() {
    const windowWidth = window.innerWidth;
    const screenWidth = window.screen.width;
    const instagramPosts = document.querySelectorAll('.instagram-post');
    
    const isDesktopModeOnMobile = 
        (windowWidth > screenWidth * 1.2) || 
        (screenWidth < 900 && windowWidth > 1000) ||
        (window.visualViewport && window.visualViewport.scale > 1.2);
    
    if (isDesktopModeOnMobile) {
        document.body.classList.add('desktop-mode');
        
        const grid = document.querySelector('.instagram-grid');
        if (grid) {
            if (screenWidth < 500) {
                grid.style.gridTemplateColumns = '1fr';
            } else {
                grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(280px, 1fr))';
            }
            grid.style.maxWidth = '100%';
            grid.style.overflow = 'hidden';
        }
        
        instagramPosts.forEach(post => {
            post.style.transform = 'none';
            post.style.width = '100%';
            post.style.minWidth = '280px';
            post.style.maxWidth = '100%';
            post.style.position = 'relative';
            post.style.zIndex = '1';
            post.style.gridRow = 'auto';
        });
    }
    
    instagramPosts.forEach((post, index) => {
        let delay;
        if (windowWidth < 576) {
            delay = (index % 6) * 80;
        } else if (windowWidth < 992) {
            delay = (index % 8) * 90;
        } else {
            delay = (index % 12) * 100;
        }
        
        post.style.animationDelay = `${delay}ms`;
    });
    
    const videos = document.querySelectorAll('video');
    videos.forEach(video => {
        if (windowWidth < 576) {
            video.setAttribute('preload', 'none');
            video.setAttribute('playsinline', '');
            video.setAttribute('controls', 'true');
        }
    });
}

window.addEventListener('load', adjustResponsiveness);
window.addEventListener('resize', adjustResponsiveness);

window.addEventListener('scroll', function() {
    if (!window.lastDesktopCheck || Date.now() - window.lastDesktopCheck > 1000) {
        detectDesktopMode();
        window.lastDesktopCheck = Date.now();
    }
});
