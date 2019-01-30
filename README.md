# APIBattle
A web service RTS that is comprised entirely of an API built in 48 hours for Global Game Jam 2019.

## What's the point?
This is based on a game that I made in 2015 called phpBattle. It used MySQL and PHP to recreate classic games like Travian or Evony but with a persistent grid based environment. It suffered massively, however, from all sql statements being hardcoded into PHP and all outputs being in HTML. It felt clunky and difficult to play as a direct result and there was no way of creating a nicer desktop client with a smoother UI.

This is the answer to that problem. This is a recreation of the base game with some balance improvements. What it lacks, however, is a client to display anything. It's just the API. The idea is that anyone can create their own client utilising this API and make it as polished as they want. I'll be creating my own example in another repo, too, with an "official" hosted server.

## Usage
### Setup
Setup a MySQL database and clone this repository to your webserver. Run setup.php and fill in your database's credentials, then press 'Install'. The tables will set themselves up after which you'll be given the option of populating with the default buildings and world generation algorithm. You don't have to use these but you do need to use _something_. At the very least you'll want to populate 'world' with 10,000 fields with some kind of default buildingType (for example "Grass").

Set cron.php to run frequently. The default content packs are balanced for this running once a minute. **(Note that this is cycling through all world tiles and all players every time it is run. This _shouldn't_ be an issue, but you're making at least 30,000 requests to the database.)** 

### Interacting with the database
All interaction from here on should be with wherver `api.php` is stored on your webserver. The following is available to you:

#### Registration
`api.php?a=login&username=*username*&password=*password` will return either the authcode of the registered account or `false`.

#### Logging in
`api.php?a=register&username=*username*&password=*password` will attempt to register a new user. Will return either the authcode of the newly registered account or `false`.

#### Notes on authcodes
Whenever you make an interaction that modifies the gameworld you'll _need_ to send the authcode too. The authcode is what the server uses to identify the user making the modification, so make sure to store that somewhere!

#### Retrieving world data
`api.php?a=get&scope=world` will give you the entire world in JSON format. The fields are as follows:
* id - The location in the grid of this tile. E.G tile at position x=20,y=1 would be id 20. Tile at position x=3,y=40 would be 303 (that isn't a mistype: the first row is 1 - 100, the second row is 101 - 200 and so on).
* buildingType - The name of the type of building on this tile. This cannot be NULL and should always be set to _something_ (see above).
* units - The number of units on this tile (can be NULL, in which case assume 0 units / unowned)
* username - The username of the player who owns this tile. If the tile is unowned then assume the owner is "Mother Nature".
* special - Special attributes for the tile. Presently this is only used to track construction, in which case a value of x,buildingType means timeToBuild,buildingType.

#### Retrieving player data

##### Getting user stats
`api.php?a=get&scope=player&type=data&authcode=*authcode*` will return in JSON format all player stats (username, authcode, gold, wood, stone, modifier, pop, food)

##### Getting a list of buildings the player can construct
`api.php?a=get&scope=player&type=buildable&authcode=*authcode*` will return in JSON format a list of all buildings that the player (specified by authcode) has the ability to build (some buildings require others existing to build, E.G barracks first requires you own a house). The fields are as follows:
* id - This just corresponds to the order that the building was added to the database. It doesn't mean anything significant.
* buildingType - The name of the building.
* goldCost - The cost of construction, in gold.
* woodCost - The cost of construction, in wood.
* stoneCost - The cost of construction, in stone.
* timeToBuild - The time (in cron cycles which by default should be once a minute) that construction takes.
* timeToDeposit - How often this building type deposits its resource **(currently unused)**
* attribute - The attribute that this building type deposits **(in future will use timeToDeposit but currently it deposits every time the cron job is run, which by default should be once a minute)**. This should be either "units" in which case it'll attempt to spawn _x_ units on its own tile or if not it'll add to the owning player _exactly the field that attribute is set to_. IE, if a House is to add to population then the field corresponding to population in the player table is _pop_ so the building attribute type is _pop_. If a farm gives food then attribute='food' etc.
* depositValue - The number of _attribute_ that is added or spawned (e.g to give 10 food a cycle attribute='food', depositValue=10)
* hp - **(currently unused)**
* requirement - Another buildingType that the player must own in order to be able to build this one. E.G with the defaultBuildings module the player must own a House in order to build a Barracks, so for Barracks requirement='House'.

##### Getting the number of buildings the player owns
`api.php?a=get&scope=player&type=buildingsum&authcode=*authcode*` - Returns a number that specifies how many buildings the player owns. E.G for a new account this would be 0.

#### Interacting with the world

##### Building things
`api.php?a=build&position=*position*&type=*buildingType*&authcode=*authcode` - Attempts to start construction of building _type_ on tile _position_. Requires user authcode. See 'Special Rules' for information on Castle building. Returns `true` if construction was a success.

##### Movement and attacking
Fights are established and settled by attempting to move units onto a tile that has enemy units already on it. As a result both the movement and attacking of units is handled by the same command.

`api.php?a=move&position=*position*&newPosition=*newPosition*&number=*number*&authcode=*authcode*` - Attempts to moev _number_ units from _position_ to _newPosition_. Requires user authcode. If the number of units specified is greater than the existing units on the tile then it'll move all units on the tile-1. Note that you must always have at least 1 unit on a tile you own. Returns `true` if movement is successful, or four numbers separated by a comma if a fight has ensued. The first number represents the number of units that the attacker has lost, the second number is the number of units the defender has lost, the third number is the number of units that tried to attack and the fourth number is the number of units on the target tile.
E.G if the api returns `2,4,10,5` it means that 10 units tried to attack 5 units and the attackers lost 2 and the defenders 4. Since 5-4=1 the tile is still owned by its original owner and the 10-2=8 units that tried to attack return to their original tile.

## Rules & Notes

This section of the readme will refer to the rules of gameplay and give insight on how mechanics work within the game. It assumes that you're using the defaultBuildings module.

### Establishing an Empire

#### Placing a Castle
The first building that a user needs to place is `Castle`. There is _no cost_ to placing this and it can go on any tile owned by Mother Nature (the `buildingsum` api function exists mainly to test whether the player is yet to place their castle, so utilise this if convenient!) Castle's instantly grant 25 units to the base tile.

##### "Why can't I build a Barracks?"
Many buildings require other buildings existing in order to begin construction. Barracks is the most apparent example of this as you must first have a fully constructed House to build it. Your client should emphasize this to prevent players from feeling as if there's some grand complex mechanism to 'unlocking' building types. 

#### Claiming territory
You need to have 1 unit on a tile to claim it and once claimed cannot move that 1 unit off until it is attacked. All tiles owned by "Mother Nature" are fair game. Owning a building type will grant you its benefits even if you weren't the one to build it. For example: Forests generate wood, Mines generate stone. These natural resources exist to be claimed and fought over!

#### Managing population
Units only generate on unit building types if a player has the `pop` to generate them. Each time a unit is created `pop` is reduced by 1, so you _need_ to have a strong and stable population in order to maintain any kind of military force. If 100 or more units are on a tile then that tile won't generate new units.

Population also generate Gold via taxes. Right now the tax rate is hardcoded at 20%. This means that, for example, if you have a population of 100 they'll generate 20 Gold per cycle. This number is always rounded down to the nearest integer.

Population consume 1 Food per cycle and if there isn't any Food for a citizen to eat they'll _instantly die_. For your population to boom you need to be generating as much food as you are generating new citizens.

#### Win state
The goal of the game is take out other player's castles. Right now nothing happens when you do so, but in the future this will cause all of their existing territory to belong to the player who currently holds their castle (and so on, if a player with 2 castles loses 1 then nothing changes so long as they hold at least 1 castle).

## Gameservers and Clients
The official server is hosted at http://freshplay.co.uk/b/api.php and uses all of the default packs. Feel free to use this in any way you want for your own client!

Here's the full list of existing clients:
* [**battleClient**, an open source project by Rthista and Pebsie built in Love2D.](https://github.com/Rithista/battleClient)

