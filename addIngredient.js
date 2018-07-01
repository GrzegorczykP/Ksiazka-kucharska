var ingredientAdded = 0;

function addIngredient() {
    var original = document.getElementById('ingredient' + ingredientAdded);
    var clone = original.cloneNode(true);
    clone.id = "ingredient" + ++ingredientAdded;
    for(var i=0;i<3;i++) clone.getElementsByTagName('input')[i].value = null;
    original.parentNode.appendChild(clone);
}

function addIngredientOne() {
    var original = document.getElementById('ingredient' + ingredientAdded);
    var clone = original.cloneNode(true);
    clone.id = "ingredient" + ++ingredientAdded;
    clone.getElementsByTagName('input')[0].value = null;
    clone.getElementsByClassName('number')[0].innerHTML = ingredientAdded+1+'.';
    original.parentNode.appendChild(clone);
}

function removeIngredient() {
    if(ingredientAdded>0) document.getElementById('ingredient' + ingredientAdded--).remove();
}