function showTab(event, tabId) {
  var tabContents = document.querySelectorAll('.tab-content');
  tabContents.forEach(function(tab) {
    tab.style.display = 'none';
  });

  var tabButtons = document.querySelectorAll('.tab-btn');
  tabButtons.forEach(function(btn) {
    btn.classList.remove('active');
  });

  var target = document.getElementById(tabId);
  if (target) {
    target.style.display = 'block';
  }

  if (event && event.currentTarget) {
    event.currentTarget.classList.add('active');
  }
}
