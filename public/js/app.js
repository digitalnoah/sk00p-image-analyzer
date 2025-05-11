// Global variables
let currentStep = 1;
let uploadedImage = null;
let currentImageData = null;

// Function to navigate between steps
function goToStep(step) {
	document.querySelectorAll(".step").forEach((s) => s.classList.remove("active"));
	document.getElementById(`step${step}`).classList.add("active");
	currentStep = step;
}

// Handle image upload
document.addEventListener("DOMContentLoaded", function () {
	// Image upload event listener
	document.getElementById("imageUpload").addEventListener("change", function (e) {
		const file = e.target.files[0];
		if (file) {
			uploadedImage = file;
			const reader = new FileReader();
			reader.onload = function (e) {
				// Update the image src instead of background-image
				const previewImg = document.getElementById("previewImage");
				const analysisImg = document.getElementById("analysisImage");

				previewImg.src = e.target.result;
				analysisImg.src = e.target.result;

				// Show the images
				previewImg.style.display = "block";
				analysisImg.style.display = "block";

				goToStep(2);
			};
			reader.readAsDataURL(file);
		}
	});

	// Add analyze event listener
	document.getElementById("step3").addEventListener("transitionend", function () {
		if (currentStep === 3 && !document.getElementById("step3").classList.contains("analyzing")) {
			analyzeImage();
		}
	});

	// Add button event listeners
	document.querySelectorAll("[data-action]").forEach((button) => {
		button.addEventListener("click", function () {
			const action = this.getAttribute("data-action");

			if (action === "upload") {
				document.getElementById("imageUpload").click();
			} else if (action === "goToStep") {
				const step = parseInt(this.getAttribute("data-step"));
				goToStep(step);
			} else if (action === "analyze") {
				document.getElementById("step3").classList.add("analyzing");
				goToStep(3);
				analyzeImage();
			}
		});
	});
});

// Function to upload to the server
async function uploadToServer(file) {
	const formData = new FormData();
	formData.append("image", file);

	try {
		const response = await fetch("ajax/upload.php", {
			method: "POST",
			body: formData,
		});
		return await response.json();
	} catch (error) {
		console.error("Upload error:", error);
		return { error: "Network error during upload" };
	}
}

// Function to analyze image
async function analyzeImage() {
	if (!uploadedImage) {
		alert("Please upload an image first");
		return;
	}

	// Show loading state
	document.getElementById("imageDescription").textContent = "Analyzing image...";
	document.getElementById("imageTags").innerHTML = "";

	try {
		// Upload the image first if not already uploaded
		let imageData;
		if (!currentImageData) {
			const uploadResult = await uploadToServer(uploadedImage);
			if (uploadResult.error) {
				throw new Error(uploadResult.error);
			}
			imageData = uploadResult;
			currentImageData = imageData;
		} else {
			imageData = currentImageData;
		}

		// Now request analysis
		const response = await fetch("ajax/analyze.php", {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify({
				image_id: imageData.image_id,
				image_url: imageData.url,
			}),
		});

		const result = await response.json();
		if (result.error) {
			throw new Error(result.error);
		}

		// Display results
		const analysis = result.analysis;
		document.getElementById("imageDescription").textContent = analysis.description;

		// Clear and populate tags container
		const tagsContainer = document.getElementById("imageTags");
		tagsContainer.innerHTML = "";

		// Process all tag types
		const tagTypes = {
			content_tags: "Content",
			style_tags: "Style",
			technical_tags: "Technical",
		};

		for (const [tagType, label] of Object.entries(tagTypes)) {
			if (analysis[tagType] && analysis[tagType].length > 0) {
				// Add section title for tag type
				tagsContainer.innerHTML += `
                    <div class="w-full mb-2">
                        <p class="section-title">${label}</p>
                    </div>
                `;

				// Add tag container
				const tagContainer = document.createElement("div");
				tagContainer.className = "flex gap-3 flex-wrap mb-4 w-full";

				// Add tags
				analysis[tagType].forEach((tag) => {
					tagContainer.innerHTML += `
                        <div class="tag tag-${tagType.split("_")[0]}">
                            <p class="tag-text">#${tag}</p>
                        </div>
                    `;
				});

				tagsContainer.appendChild(tagContainer);
			}
		}
	} catch (error) {
		console.error("Analysis error:", error);
		document.getElementById("imageDescription").textContent = "Error: " + error.message;
	} finally {
		document.getElementById("step3").classList.remove("analyzing");
	}
}
