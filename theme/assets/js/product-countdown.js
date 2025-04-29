document.addEventListener('DOMContentLoaded', () => {
    const countdown = document.getElementById("product-countdown");
	if (!countdown) return;

	const deadline = new Date(countdown.dataset.deadline).getTime();

	const interval = setInterval(() => {
		const now = new Date().getTime();
		const distance = deadline - now;

		if (distance < 0) {
			document.querySelector('.product-container').innerHTML = '<a href="#" class="product_button disabled" onclick="return false;" style="pointer-events: none; opacity: 0.4;">UNAVAILABLE</a>';
			document.querySelector('.coupon-container').style.display = 'none';
			clearInterval(interval);
			return;
		}

		const days = Math.floor(distance / (1000 * 60 * 60 * 24));
		const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		const seconds = Math.floor((distance % (1000 * 60)) / 1000);

		countdown.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
	}, 1000);
});