<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Carousel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 10000; /* Ensure it is above other content */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent black background */
            padding-top: 60px;
            animation: fadeIn 0.5s ease; /* Fade-in animation */
        }
        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(24, 23, 23, 0.73); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Blur effect */
            margin: 1% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
            color: white;
            border-radius: 10px;
            animation: slideIn 0.5s ease; /* Slide-in animation */
        }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
        .register-btn-container {
            display: flex;
            flex-direction: column; /* Stack buttons vertically */
            align-items: center; /* Center align buttons */
        }
        .register-btn, .read-more-btn {
            width: 50%; /* Make buttons take equal space */
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .register-btn {
            background-color: #4CAF50; /* Green background */
            color: white;
        }
        .register-btn:hover {
            background-color: #45a049; /* Darker green on hover */
        }
        .read-more-btn {
            background-color:rgba(37, 37, 37, 0.57); /* Blue background */
            color: white;
        }
        .read-more-btn:hover {
            background-color: #007bb5; /* Darker blue on hover */
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translate(-50%, -60%); }
            to { transform: translate(-50%, -50%); }
        }
    </style>
</head>
<body>
  <div class="heading-container flip-in" id="events">
              <h1 class="text-center display-4 font-weight-bold section-title">MaJIStic Showdowns 2k25</h1>
          </div>
<section id="events-carousel" class="zoom-in">
  <div class="swiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide swiper-slide--one">
        <span>TAAL SE TAAL MILA</span>
        <div>
          <h2>Groove to Win, Dance to Shine!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn" onclick="openModal('modal1')">Explore</button>
          </div>
        </div>
      </div>
      <div class="swiper-slide swiper-slide--two">
        <span>ACTOMANIA</span>
        <div>
          <h2>Lights, Camera, Action—Steal the Show!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn" onclick="openModal('modal2')">Explore</button>
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--three">
        <span>THE POETRY SLAM</span>
        <div>
          <h2>Words That Echo, Verses That Inspire!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn" onclick="openModal('modal3')">Explore</button>
         
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--four">
        <span>JAM ROOM</span>
        <div>
          <h2>Feel the Beat, Rock the Street!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn" onclick="openModal('modal4')">Explore</button>
            
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--five">
        <span>FASHION FIESTA</span>
        <div>
          <h2>Walk the Ramp, Own the Glam!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn" onclick="openModal('modal5')">Explore</button>
            
          </div>
        </div>
      </div>
    </div>
    <!-- Add Pagination -->
    <div class="swiper-pagination"></div>
  </div>
</section>

<!-- Modals for event details -->
<div id="modal1" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal1')">×</span>
    <h2 style="color: #ff4500; text-shadow: 2px 2px 4px #000;">Taal Se Taal Mila</h2>
    <p style="color: #fff; font-size: 16px; line-height: 1.5;">
      <strong style="color: #ff4500;">Taal-Darpana:</strong> Prepare for an *electrifying explosion* of rhythm and soul in this inter-college dance showdown! The stage blazes with *jaw-dropping choreography*, spellbinding moves, and a tidal wave of unleashed creativity. Dancers from every corner unite, fusing their fiery passion and vibrant cultural vibes into a *spectacle of motion and music* that’ll light up our annual college fest with unforgettable energy!<br><br>
      <strong style="color: #ff4500;">EVENT CO-ORDINATORS:</strong><br>Upasana Paul (+91 6291324934)
    </p>
    <button 
      style="background-color: #ff4500; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px;"
      onclick="registerForEvent('Taal Se Taal Mila')">Register Now!</button>
  </div>
</div>

<div id="modal2" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal2')">×</span>
    <h2 style="color: #8a2be2; text-shadow: 2px 2px 4px #000;">Actomania</h2>
    <p style="color: #fff; font-size: 16px; line-height: 1.5;">
      Dive into the *heart-pounding drama* of this inter-college theater clash, where the spotlight crowns the boldest storytellers and dream-weavers! With *gut-wrenching tales* and powerhouse performances, this stage ignites with raw emotion, wild creativity, and the sheer *magic of live artistry*—a highlight of our annual college fest that’ll leave you spellbound!<br><br>
      <strong style="color: #8a2be2;">EVENT CO-ORDINATORS:</strong><br>Dipanwita Lahiri (+91-8653384930)
    </p>
    <button 
      style="background-color: #8a2be2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px;"
      onclick="registerForEvent('Actomania')">Register Now!</button>
  </div>
</div>

<div id="modal3" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal3')">×</span>
    <h2 style="color: #32cd32; text-shadow: 2px 2px 4px #000;">The Poetry Slam</h2>
    <p style="color: #fff; font-size: 16px; line-height: 1.5;">
      <strong style="color: #32cd32;">Words That Echo, Verses That Inspire!</strong> Step into the *fiery arena* of our poetry slam, where raw emotions collide and powerful lines soar! Unleash your *soul-stirring verses* to a crowd buzzing with energy—every word a spark, every stanza a blaze, ready to set our annual college fest ablaze with poetic passion!
      <strong style="color: #32cd32;">EVENT CO-ORDINATORS:</strong><br> Jotisingdha Das (+91 97492 84221)
    </p>
    <button 
      style="background-color: #32cd32; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px;"
      onclick="registerForEvent('The Poetry Slam')">Register Now!</button>
  </div>
</div>

<div id="modal4" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal4')">×</span>
    <h2 style="color: #1e90ff; text-shadow: 2px 2px 4px #000;">Jam Room</h2>
    <p style="color: #fff; font-size: 16px; line-height: 1.5;">
      Get ready for a *sonic storm* in this inter-college band showdown! Musical titans collide with *earth-shaking beats* and melodies that pierce the soul, delivering a torrent of passion and ingenuity. From pulse-racing rhythms to *haunting harmonies*, this is where college bands unleash their wildest vibes in a *symphonic celebration* that’ll rock our annual college fest!<br><br>
      <strong style="color: #1e90ff;">EVENT CO-ORDINATORS:</strong><br>Ayush Agarwal (+91-6297076034)
    </p>
    <button 
      style="background-color: #1e90ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px;"
      onclick="registerForEvent('Jam Room')">Register Now!</button>
  </div>
</div>

<div id="modal5" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal5')">×</span>
    <h2 style="color: #ff69b4; text-shadow: 2px 2px 4px #000;">Fashion Fiesta</h2>
    <p style="color: #fff; font-size: 16px; line-height: 1.5;">
      <strong style="color: #ff69b4;">GlaM It Up:</strong> Strut into the *dazzling whirlwind* of this inter-college fashion face-off, where the runway transforms into a *sparkling canvas* for fearless style! Visionaries and trendsetters collide, unveiling *mind-bending designs* that dance between bold innovation and timeless grace. It’s a *glorious riot of couture*—a tribute to the wild, wondrous world of fashion artistry, set to light up our annual college fest with glamour and glory!<br><br>
      <strong style="color: #ff69b4;">EVENT CO-ORDINATORS:</strong>
    </p>
    <button 
      style="background-color: #ff69b4; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px;"
      onclick="registerForEvent('Fashion Fiesta')">Register Now!</button>
  </div>
</div>



<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>
<script>
  function registerForEvent(eventName) {
    window.open('registration_inhouse.php', '_blank');
    console.log(`Registration initiated for ${eventName}`);
}
  var swiper = new Swiper(".swiper", {
  effect: "coverflow",
  grabCursor: true,
  centeredSlides: true,
  slidesPerView: "auto",
  coverflowEffect: {
    rotate: 0,
    stretch: 0,
    depth: 100,
    modifier: 2,
    slideShadows: true
  },
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
    delay: 8000,
    disableOnInteraction: false
  }
});

// Enable page scrolling when scrolling up or down
document.querySelector('.swiper').addEventListener('wheel', function(event) {
  if (event.deltaY !== 0) {
    window.scrollBy(0, event.deltaY);
  }
});

// Zoom-in and zoom-out animation on scroll
document.addEventListener('DOMContentLoaded', function() {
  const eventsSection = document.querySelector('#events-carousel');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        eventsSection.classList.add('zoom-in');
        eventsSection.classList.remove('zoom-out');
      } else {
        eventsSection.classList.add('zoom-out');
        eventsSection.classList.remove('zoom-in');
      }
    });
  }, { threshold: 0.1 });

  observer.observe(eventsSection);
});

// Open modal
function openModal(modalId) {
  document.getElementById(modalId).style.display = "block";
}

// Close modal
function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none";
}

// Close modal when clicking outside of it
window.onclick = function(event) {
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  });
}
</script>
</body>
</html>