function toggleDropdown()
{
    document.getElementById("profileDropdown").classList.toggle("show");
}

window.onclick = function(event)
{
    if (!event.target.matches('.profile-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-menu");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}