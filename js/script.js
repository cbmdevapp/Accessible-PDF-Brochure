document.addEventListener("DOMContentLoaded", function () {
    const brochureTabLink = document.querySelector('a[href="#tab-pdf_brochure"]');
    if (brochureTabLink) {
        brochureTabLink.addEventListener("click", function () {
            const brochureContent = document.getElementById("tab-pdf_brochure");
            if (brochureContent) {
                setTimeout(() => {
                    brochureContent.scrollIntoView({ behavior: "smooth", block: "start" });
                }, 100);
            }
        });
    }
});
