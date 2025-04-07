<header class="site-header">
    <div class="header-bar">
        <div class="site-branding">
            <a href="/index.php"><img src="https://i.postimg.cc/TwP5Rrcj/jis.png" alt="Logo"></a>
            <div class="majisticheadlogo">
                <a href="/index.php"><img src="https://i.ibb.co/s9pJ72SV/majisticlogo.png" alt="Logo"></a>
            </div>
            <!-- <a href="#"><img src="../images/jislogo.png" alt="Logo"></a> -->
        </div>

        <nav class="site-navigation">
            <ul>
                <li><a href="/index.php" class="nav-link">HOME</a></li>
                <li><a href="/#events" class="nav-link">EVENTS</a></li>
                <li><a href="/merchandise.php" class="nav-link">MERCHANDIES</a></li>
                <!--<li><a href="index.php#sponsors">SPONSORS</a></li>-->
                <li><a href="/connect/" class="nav-link">CONNECT</a></li>
                <li><a href="/committee.php" class="nav-link">COMMITTEE</a></li>
                <li><a href="/about.php" class="nav-link">ABOUT</a></li>
                <li><a href="/contact.php" class="nav-link">CONTACT</a></li> 
            </ul><!-- flex -->
        </nav><!-- .site-navigation -->

        <div class="hamburger-menu d-lg-none">
            <span></span>
            <span></span>
            <span></span>
        </div><!-- .hamburger-menu -->
    </div><!-- header-bar -->
</header><!-- .site-header -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.querySelector('.hamburger-menu');
        const nav = document.querySelector('.site-navigation');
        const header = document.querySelector('.header-bar');
        const navLinks = document.querySelectorAll('.nav-link');

        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('open');
            nav.classList.toggle('show');
        });
        
        // Add click event listener to each navigation link
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('open');
                nav.classList.remove('show');
            });
        });
        
        // Add scroll event listener to change header background
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) { // After scrolling 100px
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    });
</script>