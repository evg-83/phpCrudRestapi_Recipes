# phpCrudRestapi_Recipes


Register:
http://your_host/your_nameFolder/public/auth/register

body -> form-data:

Key: username
Value: user_name

Key: email
Value: email@example.com

Key: password
Value: your_pass

Login:
http://your_host/your_nameFolder/public/auth/login

body -> form-data:

Key: username
Value: user_name

Key: password
Value: your_pass

Logout:
http://your_host/your_nameFolder/public/auth/logout

==========================================================

Recipe Get all:
http://your_host/your_nameFolder/public/recipe/get

Recipe Get by recipe id:
http://your_host/your_nameFolder/public/recipe/getByRecipeId/recipe_id

Recipe create:
http://your_host/your_nameFolder/public/recipe/create

body -> form-data:

Key: name
Value: recipe_name

Key: ingredients[0][id]
Value: ingredient_id

Key: ingredients[0][amount]
Value: ingredient_amount

...more ingredients
...

Key: steps[0]
Value: name_of_step

...more steps
...

Key: photo
Value: file

Recipe update:
http://your_host/your_nameFolder/public/recipe/update/recipe_id

body -> form-data:

Key: name
Value: recipe_name

Key: ingredients[0][id]
Value: ingredient_id

Key: ingredients[0][amount]
Value: ingredient_amount

...more ingredients
...

Key: steps[0]
Value: name_of_step

...more steps
...

Key: photo
Value: file

Recipe deleting one:
http://your_host/your_nameFolder/public/recipe/delete/recipe_id

Recipe deleting multiple:
http://your_host/your_nameFolder/public/recipe/deleteMultiple

body -> raw JSON:

[recipe_id_1, recipe_id_2, ...]

==========================================================

Ingredient Get all:
http://your_host/your_nameFolder/public/recipe/get

Ingredient Get by ingredient id:
http://your_host/your_nameFolder/public/recipe/getByIngredientId/ingredient_id

Ingredient create:
http://your_host/your_nameFolder/public/ingredient/create

body -> form-data:

Key: name
Value: ingredient_name

Key: unit
Value: unit_title

Ingredient update:
http://your_host/your_nameFolder/public/ingredient/update/ingredient_id

body -> form-data:

Key: name
Value: ingredient_name

Key: unit
Value: unit_title

Ingredient deleting one:
http://your_host/your_nameFolder/public/ingredient/delete/ingredient_id

Ingredient deleting multiple:
http://your_host/your_nameFolder/public/ingredient/deleteMultiple

body -> raw JSON:

[ingredient_id_1, ingredient_id_2, ...]


