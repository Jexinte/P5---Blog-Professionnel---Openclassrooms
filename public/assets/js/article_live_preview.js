const imageFile = document.getElementById('image-file')
const title = document.getElementById("title")
const titlePreview = document.getElementById("title-preview")
const shortPhrase = document.getElementById("short-phrase")
const shortPhrasePreview = document.getElementById("short-phrase-preview")
const content = document.getElementById("content");
const contentPreview = document.getElementById("content-preview");
let imageInput = document.getElementById("img-preview")


title.addEventListener("change",() => {
  titlePreview.textContent = title.value
})

const displayImagePreview = (input) => {
  
  if(imageFile.length != 0){
    
    let reader = new FileReader()
    reader.onload = function (e)  {
      imageInput.src=e.target.result
    }
    reader.readAsDataURL(input.files[0])
  }

}
shortPhrase.addEventListener("change",() => {
  shortPhrasePreview.textContent = shortPhrase.value
})

content.addEventListener("change",() => {
  contentPreview.textContent = content.value
})
