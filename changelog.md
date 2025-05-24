
All notable changes to this project will be documented in this file.  

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project loosely adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---
#### [2.0.18] - 2025-05-24

##### Fixed
- Ban page edge case
- Playtime on messages is now in hours

---
#### [2.0.17] - 2025-05-22

##### Fixed
- Admin roster page was broken, is now not

---
#### [2.0.16] - 2025-05-22

##### Fixed
- Major optimizations across the application. Ticket, connectionDB, and player pages should load much faster now.

---
#### [2.0.15] - 2025-05-02

##### Fixed
- The TGDB allow list function now works

---
#### [2.0.14] - 2025-04-07

##### Added 
- Admin roster
- A whois link to IP popovers for admins

##### Fixed
- IPs on telemetry showing up as ints
- An edge case with invalid round IDs being reported from upstream

##### Updated
- The privacy policy

---
#### [2.0.13] - 2025-03-24

##### Added 
- Links to feedback threads if an admin has one set
- The ability for admins to set a link to their feedback thread
- Links to bans by CID and IP on CID and IP popovers
- Admins can now (soft) delete books from the library

---
#### [2.0.12] - 2025-03-20

##### Added 
- Allow-list functionality for admin training purposes

---
#### [2.0.11] - 2025-03-17

##### Added 
- Preliminary "timeline" round pages

##### Fixed
- OAuth with the TG station forums

---
#### [2.0.10] - 2025-03-08

##### Added
- Poll results!
  
##### Fixed
- Admins can now see secret notes
- Issue with null rounds 
- Fixes to searching
- Ban listing expiration status
- Tickets not being in the correct order

---
#### [2.0.9] - 2025-03-03
  
##### Added
- A chart of all the jobs a player has played and how long they've played it
- The ability to search across multiple fields on bans and messages & notes. Sorry, admin-only for now.

##### Fixed
- Round pages throwing an SQL error
- The database for public tickets didn't actually exist
- Testing out a new paginator on rounds 
  
---
#### [2.0.8] - 2025-02-22
  
I messed up so this is an empty version.
  
---
#### [2.0.7] - 2025-02-14

##### Fixed
- Individual round routes were throwing things off so I fixed it.
- IP badge issue on some ban pages

---
#### [2.0.6] - 2025-02-13

##### Added 
- Round listing page

---
#### [2.0.5] - 2025-02-12

##### Added 
- TelemetryDB for admins
- Ban expiration time to ban listing pages

#### Fixed
- CID and IP popovers no longer incorrectly require authentication if you're logged in

---
#### [2.0.4] - 2025-02-01

##### Added 
- More information to the player page, including the number of rounds they've played and how many deaths they have
- Also added a little sparkline graph to show how many rounds they've been in recently!

##### Changed
- Excluded non- /tg/station 13 servers from server parsing
- After logging in, you will now be redirected to the page you were trying to get to in the first place
- Ban listing shows remaining time for bans that expire
- Show hours for ghost and living minutes on player pages
---
  
#### [2.0.3] - 2025-01-31

##### Added
- Theme switcher
- A listing of new players, for admins

##### Changed
- Updated home page layout

##### Fixed
- Player playtime graph rendering

---

#### [2.0.2] - 2025-01-30

##### Changed
- Public ban JSON feed is now versioned
- Added `<p>` tags to allowed HTML for library books

---

#### [2.0.1] - 2025-01-30

##### Added
- Changelog
- Changelog page

---

#### [2.0.0] - 2025-01-30

##### Added
- Statbus 2.0
