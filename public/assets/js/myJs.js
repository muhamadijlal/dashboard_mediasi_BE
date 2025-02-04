document.addEventListener('DOMContentLoaded', function() {
  const buttonProfile = document.getElementById('profile');
  const aside = document.querySelector('aside');
  const dropdownMenu = document.querySelector('summary');
  const navProfile = document.querySelector('nav');
  const backdrop = document.getElementById('backdrop');
  const hamburger = document.getElementById('hamburger');
  const sidebar = document.getElementById('sidebar');
  const links = document.querySelectorAll('details a');

  // hamburger
  hamburger.addEventListener('click', function() {
    sidebar.classList.toggle('sidebar-active');
    sidebar.classList.toggle('-left-[300px]');
    backdrop.classList.toggle("hidden")
  });

  // button profile clicked
  buttonProfile.addEventListener('click', function(event) {
    // Prevent event from bubbling up to window click listener
    event.stopPropagation();
    
    // Toggle visibility of dropdown (navProfile)
    navProfile.classList.toggle('hidden');
    
    // Toggle additional style for active profile button (optional)
    buttonProfile.classList.toggle('profile-active');
  });

  // Close the dropdown if clicked outside the profile button or navProfile
  window.addEventListener('click', function(e) {
    if (e.target !== buttonProfile && !buttonProfile.contains(e.target) && e.target !== navProfile) {
      navProfile.classList.add('hidden');
      buttonProfile.classList.remove('profile-active');
    }

    // Close sidebar if clicked outside
    if (e.target !== hamburger && e.target !== aside && !dropdownMenu.contains(e.target)) {
      sidebar.classList.remove('sidebar-active');
      sidebar.classList.add('-left-[300px]');
      backdrop.classList.add("hidden") 
    }
  });

    links.forEach(link => {
        link.addEventListener('click', function(event) {
            // Memeriksa apakah link memiliki atribut 'href' dan jika ya, lanjutkan dengan navigasi
            if (link.getAttribute('href')) {
                // Menghentikan event default hanya jika kita mengklik link yang mengarahkan ke halaman
                event.preventDefault();
    
                // Menemukan elemen details terdekat
                const details = link.closest('details');
                
                // Membalikkan status terbuka/tertutup elemen details
                details.open = !details.open;
    
                // Setelah itu, baru lakukan navigasi ke URL di href
                window.location.href = link.getAttribute('href');
            }
        });
    });
});
