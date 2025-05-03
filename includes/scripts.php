<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>




<!-- Countdown -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
  // Set the countdown target date to 21 March 2025
  const targetDate = new Date("2025-04-11T10:00:00");

  // Update countdown every second
  const countdownInterval = setInterval(function () {
    const now = new Date();
    const timeDifference = targetDate - now;

    if (timeDifference <= 0) {
      clearInterval(countdownInterval); // Stop the timer when the countdown ends
      document.querySelector(".dday").textContent = "0";
      document.querySelector(".dhour").textContent = "0";
      document.querySelector(".dmin").textContent = "0";
      document.querySelector(".dsec").textContent = "0";
      return;
    }

    // Calculate days, hours, minutes, and seconds
    const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

    // Update the DOM
    document.querySelector(".dday").textContent = days;
    document.querySelector(".dhour").textContent = hours;
    document.querySelector(".dmin").textContent = minutes;
    document.querySelector(".dsec").textContent = seconds;
  }, 1000);
});

</script>

<!-- Register Modal -->
<script>
  // Get modal and button elements
const registerModal = document.getElementById("registerModal");
const registerBtn = document.getElementById("registerBtn");
const closeBtn = document.querySelector(".close-btn");

// Open modal on button click
registerBtn.addEventListener("click", () => {
    registerModal.style.display = "block";
});

// Close modal on close button click
closeBtn.addEventListener("click", () => {
    registerModal.style.display = "none";
});

// Close modal on outside click
window.addEventListener("click", (event) => {
    if (event.target === registerModal) {
        registerModal.style.display = "none";
    }
});

</script>

<script>
    /*----------------Stats-----------------*/
    function animateCounter(element, start, end, duration) {
  let range = end - start,
      stepTime = Math.abs(Math.floor(duration / range)),
      startTime = new Date().getTime(),
      endTime = startTime + duration,
      timer;

  function run() {
    let now = new Date().getTime(),
        remaining = Math.max((endTime - now) / duration, 0),
        value = Math.round(end - remaining * range);

    element.innerHTML = value;

    if (value === end) {
      clearInterval(timer);
      element.innerHTML = value + '+';
    }
  }

  timer = setInterval(run, stepTime);
  run();
}

// Intersection Observer to trigger counters
const observer = new IntersectionObserver((entries, observer) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const box = entry.target;
      const counter = box.querySelector('.counter');
      const startValue = parseInt(box.getAttribute('data-start'), 10);
      const endValue = parseInt(box.getAttribute('data-end'), 10);
      const duration = parseInt(box.getAttribute('data-duration'), 10);

      animateCounter(counter, startValue, endValue, duration);
      observer.unobserve(box); // Stop observing once animation starts
    }
  });
}, {
  threshold: 0.5, // Trigger when 50% of the element is visible
});

// Attach the observer to each box
document.querySelectorAll('.box').forEach(box => {
  observer.observe(box);
});


    

const gallery = document.querySelector('.home-gallery .gallery');
const images = gallery.innerHTML; // Get the current images

// Duplicate the images for seamless scrolling
gallery.innerHTML += images;


const initPageTransitions = () => {
  setTimeout(() => document.body.classList.add("render"), 60);
  const navdemos = Array.from(document.querySelectorAll(".demos__links .demo"));
  const total = navdemos.length;
  const current = navdemos.findIndex((el) =>
    el.classList.contains("demo--current")
  );
  const navigate = (linkEl) => {
    document.body.classList.remove("render");
    document.body.addEventListener(
      "transitionend",
      () => (window.location = linkEl.href)
    );
  };
  navdemos.forEach((link) =>
    link.addEventListener("click", (ev) => {
      ev.preventDefault();
      navigate(ev.currentTarget);
    })
  );
  document.addEventListener("keydown", (ev) => {
    const keyCode = ev.keyCode || ev.which;
    let linkEl;
    if (keyCode === 37) {
      linkEl = current > 0 ? navdemos[current - 1] : navdemos[total - 1];
    } else if (keyCode === 39) {
      linkEl = current < total - 1 ? navdemos[current + 1] : navdemos[0];
    } else {
      return false;
    }
    navigate(linkEl);
  });
};

// export default initPageTransitions;

class Demo2 {
  constructor() {
    initPageTransitions();
    this.initDemo();
    this.initSwiper();
    window.lazySizes.init();
  }

  initDemo() {
    const { Back } = window;
    this.cursor = document.querySelector(".arrow-cursor");
    this.cursorIcon = document.querySelector(".arrow-cursor__icon");
    this.cursorBox = this.cursor.getBoundingClientRect();
    this.easing = Back.easeOut.config(1.7);
    this.animationDuration = 0.3;
    this.cursorSide = null; // will be "left" or "right"
    this.cursorInsideSwiper = false;

    // initial cursor styling
    TweenMax.to(this.cursorIcon, 0, {
      rotation: -135,
      opacity: 0,
      scale: 0.5
    });

    document.addEventListener("mousemove", (e) => {
      this.clientX = e.clientX;
      this.clientY = e.clientY;
    });

    const render = () => {
      TweenMax.set(this.cursor, {
        x: this.clientX,
        y: this.clientY
      });
      requestAnimationFrame(render);
    };
    requestAnimationFrame(render);

    // move cursor from left to right or right to left inside the Swiper
    const onSwitchSwiperSides = () => {
      if (this.cursorInsideSwiper) {
        TweenMax.to(this.cursorIcon, this.animationDuration, {
          rotation: this.cursorSide === "right" ? -180 : 0,
          ease: this.easing
        });
        this.cursorSide = this.cursorSide === "left" ? "right" : "left";
      }

      if (!this.cursorInsideSwiper) {
        this.cursorInsideSwiper = true;
      }
    };

    const swiperContainer = document.querySelector(".swiper-container");
    swiperContainer.addEventListener("mouseenter", onSwiperMouseEnter);
    swiperContainer.addEventListener("mouseleave", onSwiperMouseLeave);

    const swiperButtonPrev = document.querySelector(".swiper-button-prev");
    const swiperButtonNext = document.querySelector(".swiper-button-next");
    swiperButtonPrev.addEventListener("mouseenter", onSwitchSwiperSides);
    swiperButtonNext.addEventListener("mouseenter", onSwitchSwiperSides);
  }

  initSwiper() {
    const { Swiper } = window;
    this.swiper = new Swiper(".swiper-container", {
      loop: true,
      slidesPerView: "auto",
      spaceBetween: 40,
      centeredSlides: true,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      }
    });
    this.swiper.on("touchMove", (e) => {
      const { clientX, clientY } = e;
      this.clientX = clientX;
      this.clientY = clientY;

      this.cursorSide = this.clientX > window.innerWidth / 2 ? "right" : "left";

      TweenMax.to(this.cursorIcon, this.animationDuration, {
        rotation: this.cursorSide === "right" ? 0 : -180,
        ease: this.easing
      });
    });

    this.bumpCursorTween = TweenMax.to(this.cursor, 0.1, {
      scale: 0.85,
      onComplete: () => {
        TweenMax.to(this.cursor, 0.2, {
          scale: 1,
          ease: this.easing
        });
      },
      paused: true
    });

    this.swiper.on("slideChange", () => {
      this.bumpCursorTween.play();
    });
  }
}

const demo2 = new Demo2();



// Get the root and marquee elements
const root = document.documentElement;
const marqueeContent = document.querySelector("ul.marquee-content");

// Get the number of elements to display from CSS variable
const marqueeElementsDisplayed = getComputedStyle(root).getPropertyValue("--marquee-elements-displayed");

// Set the CSS variable for the number of marquee elements based on the children of the marquee content
root.style.setProperty("--marquee-elements", marqueeContent.children.length);

// Clone and append elements to create a seamless loop
const totalOriginalElements = marqueeContent.children.length;

// Clone the elements until we have enough to fill the viewport and beyond
for (let i = 0; i < totalOriginalElements * 2; i++) {
  // Append each element, but loop back to the first element if we exceed the total
  marqueeContent.appendChild(marqueeContent.children[i % totalOriginalElements].cloneNode(true));
}

window.addEventListener("click", function(event) {
    if (event.target == modal) {
        closeModal();
    }
});

// Create Intersection Observer for the footer section
const footerSection = document.querySelector('footer');
const footerObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && modalShownCount < maxShowCount) {
            // Show the modal when footer section is visible
            // and modal hasn't been shown the maximum number of times
            openModal();
        }
    });
}, { threshold: 0.2 }); // Trigger when 20% of the footer section is visible

// Start observing the footer section
if (footerSection) {
    footerObserver.observe(footerSection);
}

// Generate confetti for the thank you banner
function generateConfetti() {
    const container = document.getElementById('confetti-container');
    if (!container) return;
    
    const colors = ['#FF5757', '#47A0FF', '#FFDE59', '#7ED957', '#FF57E4', '#5D5FEF'];
    const confettiCount = 50;
    
    // Clear existing confetti
    container.innerHTML = '';
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.classList.add('confetti');
        
        // Random styling
        const color = colors[Math.floor(Math.random() * colors.length)];
        const left = Math.random() * 100;
        const width = Math.random() * 8 + 3;
        const height = Math.random() * 10 + 5;
        const animationDuration = Math.random() * 3 + 2;
        const animationDelay = Math.random() * 5;
        
        // Apply styles
        confetti.style.backgroundColor = color;
        confetti.style.left = `${left}%`;
        confetti.style.width = `${width}px`;
        confetti.style.height = `${height}px`;
        confetti.style.animationDuration = `${animationDuration}s`;
        confetti.style.animationDelay = `${animationDelay}s`;
        confetti.style.opacity = Math.random() * 0.7 + 0.3;
        
        // Add to container
        container.appendChild(confetti);
    }
}

// Call once on page load
document.addEventListener('DOMContentLoaded', function() {
    generateConfetti();
    
    // Regenerate confetti periodically
    setInterval(generateConfetti, 10000);
});
</script>