document.addEventListener("DOMContentLoaded", () => {
	(async () => {
		let formData = new FormData();
		formData.append("action", __CONFIG__.action);
		formData.append("cue", __CONFIG__.cue);
		formData.append("paymentid", __CONFIG__.paymentid);

		const rawResponse = await fetch(__CONFIG__.ajaxurl, {
			method: "POST",
			body: formData,
		});
		const response = await rawResponse.json();

		if (response.status == "success") {
			window.location.href = response.url;
		} else if (response.status == "processed") {
			document.querySelector("#error_message").innerHTML = response.message;
			document.querySelector(".btn").style.display = "block";
		} else {
			document.querySelector("#error_message").innerHTML = response.message;
			document.querySelector(".processing__container").style.display = "none";
			document.querySelector(".error__container").style.display = "flex";
		}
	})();
});

// document.addEventListener('DOMContentLoaded', function() {
// 	const urlParams = new URLSearchParams(window.location.search);
// 	const myParam = urlParams.get('cue');
// });
