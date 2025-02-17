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
            z-index: 1;
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
              <h1 class="text-center display-4 font-weight-bold section-title">EVENTS</h1>
          </div>
<section id="events-carousel" class="zoom-in">
  <div class="swiper">
    <div class="swiper-wrapper">
      <div class="swiper-slide swiper-slide--one">
        <span>TAAL SE TAAL MILA</span>
        <div>
          <h2>Groove to Win, Dance to Shine!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
            <button class="read-more-btn mb-4" onclick="openModal('modal1')">Read More</button>
          </div>
        </div>
      </div>
      <div class="swiper-slide swiper-slide--two">
        <span>ACTOMANIA</span>
        <div>
          <h2>Lights, Camera, Action—Steal the Show!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
            <button class="read-more-btn mb-4" onclick="openModal('modal2')">Read More</button>
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--three">
        <span>THE POETRY SLAM</span>
        <div>
          <h2>Words That Echo, Verses That Inspire!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
            <button class="read-more-btn mb-4" onclick="openModal('modal3')">Read More</button>
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--four">
        <span>JAM ROOM</span>
        <div>
          <h2>Feel the Beat, Rock the Street!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
            <button class="read-more-btn mb-4" onclick="openModal('modal4')">Read More</button>
          </div>
        </div>
      </div>

      <div class="swiper-slide swiper-slide--five">
        <span>FASHION FIESTA</span>
        <div>
          <h2>Walk the Ramp, Own the Glam!</h2>
          <div class="register-btn-container">
            <button id="registerBtn" class="register-btn">Register</button>
            <button class="read-more-btn mb-4" onclick="openModal('modal5')">Read More</button>
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
    <span class="close" onclick="closeModal('modal1')">&times;</span>
    <h2>Taal Se Taal Mila</h2>
    <p>Groove to Win, Dance to Shine! Join us for an electrifying dance competition where you can showcase your moves and win amazing prizes.</p>
  </div>
</div>

<div id="modal2" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal2')">&times;</span>
    <h2>Actomania</h2>
    <p>Lights, Camera, Action—Steal the Show! Participate in our drama competition and let your acting skills shine on stage.</p>
  </div>
</div>

<div id="modal3" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal3')">&times;</span>
    <h2>The Poetry Slam</h2>
    <p>Words That Echo, Verses That Inspire! Join our poetry slam and share your powerful verses with an enthusiastic audience.</p>
  </div>
</div>

<div id="modal4" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal4')">&times;</span>
    <h2>Jam Room</h2>
    <p>Feel the Beat, Rock the Street! Participate in our band competition and let your music resonate with the crowd.</p>
  </div>
</div>

<div id="modal5" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('modal5')">&times;</span>
    <h2>Fashion Fiesta</h2>
    <p>Walk the Ramp, Own the Glam! Join our fashion show and showcase your style and elegance on the ramp.</p>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>
<script>
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
    delay: 3000,
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