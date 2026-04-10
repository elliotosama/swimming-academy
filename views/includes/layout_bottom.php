</div><!-- /.page -->

<script>
    const container = document.getElementById('bubbles');
    for (let i = 0; i < 14; i++) {
        const b = document.createElement('div');
        b.className = 'bubble';
        const size = 6 + Math.random() * 22;
        b.style.cssText = `width:${size}px;height:${size}px;left:${Math.random()*100}%;animation-duration:${7+Math.random()*14}s;animation-delay:${Math.random()*12}s;`;
        document.getElementById('bubbles').appendChild(b);
    }


  const toggle = document.getElementById('navToggle');
  const links  = document.querySelector('.nav-links');
  toggle.addEventListener('click', () => links.classList.toggle('open'));
  document.addEventListener('click', e => {
    if (!toggle.contains(e.target) && !links.contains(e.target))
      links.classList.remove('open');
  });
</script>
</body>
</html>