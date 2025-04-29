document.addEventListener('DOMContentLoaded', function () {
    const gallery = document.querySelector('.gallery-thumbs');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');
	const [leftFade, rightFade] = document.querySelectorAll('.gallery-fade');

	let scrollDirection = 0;
    let animationFrame;
    let holdTimeout;

    const scrollStep = 8;       // Hold-scroll speed.
    const clickJump = 100;      // Jump amount on single click.
    const holdDelay = 150;      // Time (ms) before it becomes a hold.

    function scrollLoop() {
        if (scrollDirection !== 0) {
            gallery.scrollLeft += scrollStep * scrollDirection;
            animationFrame = requestAnimationFrame(scrollLoop);
        }
    }

    function startScrolling(direction) {
        scrollDirection = direction;
        scrollLoop();
    }

    function stopScrolling() {
        scrollDirection = 0;
        cancelAnimationFrame(animationFrame);
    }

    function handlePress(btn, direction) {
        // Single click fallback.
        holdTimeout = setTimeout(() => {
            startScrolling(direction); // Start smooth scroll if held.
        }, holdDelay);

        const handleRelease = () => {
            clearTimeout(holdTimeout);

            // If it didn't transition into hold scrolling, do a quick jump.
            if (scrollDirection === 0)
                gallery.scrollLeft += clickJump * direction;

            stopScrolling();

            document.removeEventListener('mouseup', handleRelease);
            document.removeEventListener('mouseleave', handleRelease);
            document.removeEventListener('touchend', handleRelease);
        };

        document.addEventListener('mouseup', handleRelease);
        document.addEventListener('mouseleave', handleRelease);
        document.addEventListener('touchend', handleRelease);
    }

    prevBtn.addEventListener('mousedown', () => handlePress(prevBtn, -1));
    nextBtn.addEventListener('mousedown', () => handlePress(nextBtn, 1));
    prevBtn.addEventListener('touchstart', () => handlePress(prevBtn, -1));
    nextBtn.addEventListener('touchstart', () => handlePress(nextBtn, 1));
	
	// Disables the carousel if content doesn't overflow.
    const checkOverflow = () => {
        const scrollWidth = gallery.scrollWidth;
        const clientWidth = gallery.clientWidth;

        if (scrollWidth <= clientWidth) {
            // Not overflowing — hide buttons.
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
			
			// Hides fade effect.
			leftFade.style.display = 'none';
			rightFade.style.display = 'none';

            // Prevents scrolling
            gallery.style.overflowX = 'hidden';
        } else {
            // Overflowing — show buttons.
            prevBtn.style.display = '';
            nextBtn.style.display = '';
            gallery.style.overflowX = 'auto';
        }

        // Centers the carousel when the mobile layout kicks in, which can happen at 
        // either 1200px or 1025px depending on the amount of images in the carousel.
        gallery.style.justifyContent = 
            window.getComputedStyle(document.querySelector('.custom-product-wrapper')).flexDirection === 'column'
                ? 'center'
                : 'left';
    };

    checkOverflow();
    window.addEventListener('resize', checkOverflow);
	
	// Hides fade effect on the side that got scrolled to the end.
	function updateFadeVisibility() {
		const scrollLeft = gallery.scrollLeft;
		const scrollWidth = gallery.scrollWidth;
		const clientWidth = gallery.clientWidth;

		if (leftFade)
			leftFade.style.display = scrollLeft > 5 ? 'block' : 'none';

		if (rightFade)
			// scrollWidth - clientWidth is the max scrollLeft possible.
			rightFade.style.display = scrollLeft < scrollWidth - clientWidth - 5 ? 'block' : 'none';
	}
	
	updateFadeVisibility();
	gallery.addEventListener('scroll', updateFadeVisibility);
});