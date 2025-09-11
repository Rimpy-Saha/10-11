  const data_interval = document.querySelector('#homeBannerCarousel').getAttribute('data-interval');
  var data_interval_sec = data_interval / 1000; // carousel data interval in seconds


  /* if you hover/unhover as the slide's timer ends, it bugs out */

  // Wait for DOM to be ready
  document.addEventListener('DOMContentLoaded', function() {
    var $carousel = $('#homeBannerCarousel');

    // Store original Bootstrap carousel methods
    var originalPause = $.fn.carousel.Constructor.prototype.pause;
    var originalCycle = $.fn.carousel.Constructor.prototype.cycle;

    const originalInterval = $carousel.data('interval') || 10000;
    
    // 2. State tracking
    let remainingTime = originalInterval;
    let cycleTimer;
    let pauseTime;
    let startTime;
    let isPaused = false;

    // 3. Initialize with manual control
    $carousel.carousel({
      interval: false, // Disable Bootstrap's interval
      pause: false    // Disable Bootstrap's pause
    });

    // 4. Start our custom cycle
    function startCycle() {
      if (isPaused) return;
      startTime = Date.now();
      clearTimeout(cycleTimer);
      cycleTimer = setTimeout(() => {
        $carousel.carousel('next');
        remainingTime = originalInterval;
        startCycle();
      }, remainingTime);
    }

    // 5. Handle pause/resume
    $carousel
      .on('mouseenter', () => {
        if (!isPaused) {
          clearTimeout(cycleTimer);
          pauseTime = Date.now();
          remainingTime = Math.max(0, remainingTime - (pauseTime - startTime));  
          //console.log('calculated remainingTime: '+remainingTime);  
          isPaused = true;
          $carousel.addClass('paused');
        }
      })
      .on('mouseleave', () => {
        if (isPaused) {
          isPaused = false;
          $carousel.removeClass('paused');
          startCycle();
        }
      });

    // 6. Initial start

    startCycle();

    document.documentElement.style.setProperty('--carousel-interval', `${data_interval_sec}s`);
    var activeIndicator = document.querySelector('#homeBannerCarousel .carousel-indicators li.active');
    activeIndicator.classList.remove('animate'); // Reset
    void activeIndicator.offsetWidth; // Trigger reflow (forces browser to recognize the reset)
    activeIndicator.classList.add('animate'); // Start animation fresh

    // 7. Optional: Keep manual controls working
    $('[data-bs-slide="prev"], [data-bs-slide="next"], .carousel-indicators > li').click(() => {
      clearTimeout(cycleTimer);
      remainingTime = originalInterval; // Reset for next cycle
      startCycle();
    });
  


  });
