// Mobile Dropdown für Wiki-Tabs
(function(){
  function createDropdownFromTabs() {
    var tabsContainer = document.querySelector('.psource_wiki_tabs');
    if (!tabsContainer) return;
    // Finde alle Links in beiden ULs
    var links = tabsContainer.querySelectorAll('ul.left li a, ul.right li a');
    if (!links.length) return;
    // Dropdown nur auf kleinen Bildschirmen anzeigen
    if (window.innerWidth > 700) return;
    // Dropdown nicht doppelt einfügen
    if (document.getElementById('psource-wiki-tabs-dropdown')) return;

    var select = document.createElement('select');
    select.id = 'psource-wiki-tabs-dropdown';
    select.style.width = '100%';
    select.style.margin = '10px 0';
    // Option für jede Tab
    links.forEach(function(link) {
      var option = document.createElement('option');
      option.value = link.href;
      option.textContent = link.textContent;
      if (link.parentElement.classList.contains('current')) {
        option.selected = true;
      }
      select.appendChild(option);
    });
    select.addEventListener('change', function() {
      window.location.href = this.value;
    });
    // Tabs ausblenden, Dropdown einfügen
    tabsContainer.style.display = 'none';
    tabsContainer.parentNode.insertBefore(select, tabsContainer);
  }
  document.addEventListener('DOMContentLoaded', createDropdownFromTabs);
  window.addEventListener('resize', function() {
    var select = document.getElementById('psource-wiki-tabs-dropdown');
    var tabsContainer = document.querySelector('.psource_wiki_tabs');
    if (window.innerWidth <= 700) {
      if (!select) createDropdownFromTabs();
    } else {
      if (select && tabsContainer) {
        select.remove();
        tabsContainer.style.display = '';
      }
    }
  });
})();