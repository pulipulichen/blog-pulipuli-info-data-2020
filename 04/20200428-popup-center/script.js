document.getElementById('popupRaw').addEventListener('click', function () {
  popupCenterRaw('//blog.pulipuli.info')
})

document.getElementById('popupRedirect').addEventListener('click', function () {
  popupCenterRedirect('//blog.pulipuli.info')
})

window.moveTo(0,0)
window.resizeTo(screen.availWidth, screen.availHeight)