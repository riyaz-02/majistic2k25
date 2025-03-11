<section id="proshows-carousel" class="fade-in">
  <div class="swiper1">
    <div class="swiper-wrapper">
      <div class="swiper-slide">
        <div class="gradient"></div>
        <h1 data-animate="bottom" class="title">Snigdhajit and Liveline</h1>
        <div class="artist-info-box">
          <div class="info-header">
            <span class="artist-tag">Featured Artist</span>
            <h3 class="info-title">About the Performance</h3>
          </div>
          <div class="info-content">
            <div class="info-details">
              <div class="info-item"><i class="icon"><ion-icon name="calendar-outline"></ion-icon></i> March 15, 2025</div>
              <div class="info-item"><i class="icon"><ion-icon name="time-outline"></ion-icon></i> 7:00 PM Onwards</div>
              <div class="info-item"><i class="icon"><ion-icon name="musical-notes-outline"></ion-icon></i> Modern Fusion</div>
              <div class="info-item"><i class="icon"><ion-icon name="location-outline"></ion-icon></i> Main Stage</div>
            </div>
            <div class="info-description">
              <p>Experience the sonic alchemy of Snigdhajit and Liveline, where traditional melodies meet contemporary beats. Their groundbreaking fusion style has captivated audiences across the country with heart-pounding rhythms and soaring vocals.</p>
              <p>Prepare for an unforgettable night as they debut exclusive tracks from their upcoming album alongside fan favorites that have defined modern Indian music.</p>
            </div>
          </div>
        </div>
        <img class="hero" src="../images/carousel/snig.png" alt="Snigdhajit and Liveline" loading="lazy" />
      </div>
      <div class="swiper-slide">
        <div class="gradient"></div>
        <h1 data-animate="bottom" class="title">Somlata and Aces</h1>
        <div class="artist-info-box">
          <div class="info-header">
            <span class="artist-tag">Headliner</span>
            <h3 class="info-title">About the Performance</h3>
          </div>
          <div class="info-content">
            <div class="info-details">
              <div class="info-item"><i class="icon"><ion-icon name="calendar-outline"></ion-icon></i> March 16, 2025</div>
              <div class="info-item"><i class="icon"><ion-icon name="time-outline"></ion-icon></i> 8:00 PM Onwards</div>
              <div class="info-item"><i class="icon"><ion-icon name="musical-notes-outline"></ion-icon></i> Folk Fusion</div>
              <div class="info-item"><i class="icon"><ion-icon name="location-outline"></ion-icon></i> Main Stage</div>
            </div>
            <div class="info-description">
              <p>Somlata and Aces have redefined the Bengali music scene with their innovative approach to traditional folk sounds. Their performances blend cultural heritage with contemporary arrangements that speak to both the heart and soul.</p>
              <p>This exclusive show features their award-winning compositions that have topped charts across Eastern India, promising an immersive journey through emotion and melody.</p>
            </div>
          </div>
        </div>
        <img class="hero" src="../images/carousel/somlata.png" alt="Somlata and Aces" loading="lazy" />
      </div>
      <div class="swiper-slide">
        <div class="gradient"></div>
        <h1 data-animate="bottom" class="title">TRAP</h1>
        <div class="artist-info-box">
          <div class="info-header">
            <span class="artist-tag">Special Guest</span>
            <h3 class="info-title">About the Performance</h3>
          </div>
          <div class="info-content">
            <div class="info-details">
              <div class="info-item"><i class="icon"><ion-icon name="calendar-outline"></ion-icon></i> March 17, 2025</div>
              <div class="info-item"><i class="icon"><ion-icon name="time-outline"></ion-icon></i> 9:00 PM Onwards</div>
              <div class="info-item"><i class="icon"><ion-icon name="musical-notes-outline"></ion-icon></i> Electro Pop</div>
              <div class="info-item"><i class="icon"><ion-icon name="location-outline"></ion-icon></i> Main Stage</div>
            </div>
            <div class="info-description">
              <p>TRAP pushes boundaries with their genre-defying sound that merges electronic production with raw acoustic elements. Their high-energy performances have made them festival favorites and streaming sensations.</p>
              <p>Get ready for a visual and sonic spectacle as they bring their critically acclaimed live show featuring stunning visuals, interactive elements, and bass-heavy anthems that will keep you moving all night.</p>
            </div>
          </div>
        </div>
        <img class="hero" src="../images/carousel/trap.png" alt="TRAP" loading="lazy" />
      </div>
    </div>
  </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js" type="module"></script>
<script src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js" nomodule></script>
<script>
  var swiper = new Swiper(".swiper1", {
    effect: "fade",
    fadeEffect: {
      crossFade: true
    },
    grabCursor: true,
    centeredSlides: true,
    slidesPerView: "auto",
    keyboard: {
      enabled: true
    },
    mousewheel: {
      forceToAxis: true,
      releaseOnEdges: true
    },
    spaceBetween: 60,
    loop: true,
    pagination: {
      el: ".swiper-pagination",
      clickable: true
    },
    autoplay: {
      delay: 5000, // Auto change slides every 5 seconds
      disableOnInteraction: false
    }
  });

  // Fade-in and fade-out animation on scroll
  document.addEventListener('DOMContentLoaded', function() {
    const proshowsSection = document.querySelector('#proshows-carousel');
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          proshowsSection.classList.add('visible');
        } else {
          proshowsSection.classList.remove('visible');
        }
      });
    }, { threshold: 0.1 });

    observer.observe(proshowsSection);
  });
</script>
<style>
  .fade-in {
    opacity: 0;
    transition: opacity 1s ease-in-out;
  }
  .fade-in.visible {
    opacity: 1;
  }
  .artist-info-box {
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    padding: 15px;
    border-radius: 10px;
    margin-top: 15px;
    max-width: 90%;
  }
  .artist-info-box .info-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }
  .artist-info-box .info-header .artist-tag {
    background: #ff4081;
    color: #fff;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.9em;
  }
  .artist-info-box .info-header .info-title {
    font-size: 1.2em;
    font-weight: bold;
  }
  .artist-info-box .info-content {
    display: flex;
    flex-direction: column;
  }
  .artist-info-box .info-details {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 10px;
  }
  .artist-info-box .info-details .info-item {
    display: flex;
    align-items: center;
    margin-right: 10px;
    margin-bottom: 5px;
  }
  .artist-info-box .info-details .info-item .icon {
    margin-right: 5px;
  }
  .artist-info-box .info-description {
    font-size: 0.9em;
    line-height: 1.5;
  }
  @media (max-width: 768px) {
    .artist-info-box {
      max-width: 100%;
    }
    .artist-info-box .info-details {
      flex-direction: column;
    }
    .artist-info-box .info-details .info-item {
      margin-right: 0;
    }
  }
</style>
