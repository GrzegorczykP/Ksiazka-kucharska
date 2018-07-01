var ingredientAdded = 0;

function addIngredient() {
    var original = document.getElementById('ingredient' + ingredientAdded);
    var clone = original.cloneNode(true);
    clone.id = "ingredient" + ++ingredientAdded;
    original.parentNode.appendChild(clone);
}

function removeIngredient() {
    if(ingredientAdded>0) document.getElementById('ingredient' + ingredientAdded--).remove();
}