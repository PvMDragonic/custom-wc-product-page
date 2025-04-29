document.addEventListener('DOMContentLoaded', function () {
	const couponElements = document.querySelectorAll('.coupon-text');

	couponElements.forEach(function(elem) {
		elem.addEventListener('click', function () {
			navigator.clipboard.writeText(this.innerText.trim()).catch(
				err => console.error("Error copying coupon to clipboard.", err)
			);
		});
	});
});