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
#### Registration & Logging in
`api.php?a=login&username=*username*&password=*password` will return either the authcode of the registered account or `false`.

`api.php?a=register&username=*username*&password=*password` will attempt to register a new user. Will return either the authcode of the newly registered account or `false`.

Whenever you make an interaction that modifies the gameworld you'll _need_ to send the authcode too. The authcode is what the server uses to identify the user making the modification, so make sure to store that somewhere!

#### Retrieving world data
`api.php?a=get&scope=world` will give you the entire world in JSON format. The fields are as follows:
* id - The location in the grid of this tile. E.G tile at position x=20,y=1 would be id 20. Tile at position x=3,y=40 would be 303 (that isn't a mistype: the first row is 1 - 100, the second row is 101 - 200 and so on).
* buildingType - The name of the type of building on this tile. This cannot be NULL and should always be set to _something_ (see above).
* units - The number of units on this tile (can be NULL, in which case assume 0 units / unowned)
* username - The username of the player who owns this tile. If the tile is unowned then assume the owner is "Mother Nature".
* special - Special attributes for the tile. Presently this is only used to track construction, in which case a value of x,buildingType means timeToBuild,buildingType.

#### Retrieving player data
`api.php?a=get&scope=player&type=data&authcode=*authcode*` will return in JSON format all player stats (username, authcode, gold, wood, stone, modifier, pop, food)

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

## Offical server
The official server is hosted at http://freshplay.co.uk/b/api.php
