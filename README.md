# CS3012 - Software Engineering Project  
This repository contains the backend code written for the 3rd year software engineering module.    

It consists of a pure php API (No framework) with accompanying SQL queries (No ORM).    

*Note:* This code is not secure. It was built in a short amount of time for a prototype project and as such does not have a fully fledged login system. User's database IDs are used in place of a token in communication with the front end, an issue which would have to be removed if used outside of prototype.

## Usage:  
`https://[api-server-address]/api.php?op=[desired_operation][&key=value]`  
Where `[&key=value]` represents a list of parameters in that format depending on what the given request expects.

# java-sdk  
This directory contains the supporting java SDK I developed for use by the android team.
