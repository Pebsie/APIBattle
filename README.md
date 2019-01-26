# APIBattle
A web service RTS that is comprised entirely of an API built in 48 hours for Global Game Jam 2019.

## What's the point?
This is based on a game that I made in 2015 called phpBattle. It used MySQL and PHP to recreate classic games like Travian or Evony but with a persistent grid based environment. It suffered massively, however, from all sql statements being hardcoded into PHP and all outputs being in HTML. It felt clunky and difficult to play as a direct result and there was no way of creating a nicer desktop client with a smoother UI.

This is the answer to that problem. This is a recreation of the base game with some balance improvements. What it lacks, however, is a client to display anything. It's just the API. The idea is that anyone can create their own client utilising this API and make it as polished as they want. I'll be creating my own example in another repo, too, with an "official" hosted server.

## Usage
### Setup
Setup a MySQL database and clone this repository to your webserver. Run setup.php and fill in your database's credentials, then press 'Install'. The tables will set themselves up after which you'll be given the option of populating with the default buildings and world generation algorithm. You don't have to use these but you do need to use _something_.

### Interacting with the database
All interaction from here on should be with wherver `api.php` is stored on your webserver. The following is available to you:
#### Registration & Logging in
`api.php?a=login&username=*username*&password=*password` will return either the authcode of the registered account or `false`.

`api.php?a=register&username=*username*&password=*password` will attempt to register a new user. Will return either the authcode of the newly registered account or `false`.

Whenever you make an interaction that modifies the gameworld you'll _need_ to send the authcode too. The authcode is what the server uses to identify the user making the modification.

#### Retrieving world data
`api.php?a=get&scope=world&type=buildings` will give you a list in JSON format of all the buildings currently existing in the gameworld with position, owner and HP information. The gameworld should be a 100x100 grid with the top left most tile being position 1 and the bottom right being position 10000.

#### Retrieving player data
`api.php?a=get&scope=player&type=data&authcode=*authcode*` will return in JSON format all player stats (username, authcode, gold, wood, stone, modifier, pop, food)

`api.php?a=get&scope=player&type=buildable&authcode=*authcode*` will return in JSON format a list of all buildings that the player (specified by authcode) has the ability to build (some buildings require others existing to build, E.G barracks first requires you own a house).

## Offical server
The official server is hosted at http://freshplay.co.uk/b/api.php
