<?php
?>
<style>
  body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    margin: 0;
    padding-bottom: 70px; /* Height of the footer */
  }

  main {
    flex: 1 0 auto;
  }

  .footer {
    flex-shrink: 0;
    height: 70px;
    width: 100%;
    background-color: #212529;
    color: white;
    padding: 25px 0;
    text-align: center;
    position: fixed;
    bottom: 0;
    left: 0;
    z-index: 1000;
    transition: transform 0.3s ease-in-out;
    transform: translateY(100%);
  }

  .footer.show {
    transform: translateY(0);
  }

  @media (max-width: 768px) {
    body {
      padding-bottom: 90px; /* Slightly larger padding for mobile */
    }
    
    .footer {
      height: 90px;
      padding: 15px 0;
    }
  }
</style>
<footer class="footer">
  <div class="container">
    <p class="mb-0">&copy; <?php echo date('Y'); ?> Pawfect Supplies. All rights reserved.</p>
  </div>
</footer>

<script>
  const footer = document.querySelector('.footer');
  const threshold = 100; // Distance from bottom to show footer

  window.addEventListener('scroll', function() {
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = Math.max(
      document.body.scrollHeight,
      document.documentElement.scrollHeight,
      document.body.offsetHeight,
      document.documentElement.offsetHeight
    );

    // Show footer when near bottom of page
    if (windowHeight + scrollPosition >= documentHeight - threshold) {
      footer.classList.add('show');
    } else {
      footer.classList.remove('show');
    }
  });

  // Show footer on page load if already at bottom
  window.addEventListener('load', function() {
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = Math.max(
      document.body.scrollHeight,
      document.documentElement.scrollHeight,
      document.body.offsetHeight,
      document.documentElement.offsetHeight
    );

    if (windowHeight + scrollPosition >= documentHeight - threshold) {
      footer.classList.add('show');
    }
  });
</script>
