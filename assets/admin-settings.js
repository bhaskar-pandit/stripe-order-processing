document.addEventListener('DOMContentLoaded', function () {
    // Add option button click event
    document.getElementById('add-option').addEventListener('click', function (event) {
        event.preventDefault();
        var BlankContainer = document.getElementById('repeatable_options_woocommerce_settings');
        var DataContainer = document.getElementById('repeatable_options_woocommerce_data');
        var cloneContent = BlankContainer.cloneNode(true);
        cloneContent.removeAttribute('id')
        var newIndex = DataContainer.children.length; // Get the index for the new option

        // Convert the HTML content to a string
        var cloneHTML = cloneContent.innerHTML;

        // Replace __ID__ with new text
        var newHTML = cloneHTML.replace(/__ID__/g, newIndex); // Modify 'new text' as needed

        // Create a temporary div to hold the modified HTML
        var tempDiv = document.createElement('table');
        tempDiv.className = "form-table";
        tempDiv.style = "border-top: 1px dashed #0f0f0f;";
        tempDiv.innerHTML = newHTML;



        // Append the modified HTML content
        DataContainer.appendChild(tempDiv);
    });

    // Remove option button click event
    document.addEventListener('click', function (event) {
        if (event.target && event.target.classList.contains('remove-option')) {
            event.preventDefault();
            event.target.parentElement.closest('table').remove();
        }
    });
});