# bold-phylodiv-scripts
Scripts to calculate phylodiversity and its distribution from BOLD DNA barcode data.

## Usage
Call the PHP script `phylodiv.php` from the PHP executable with a taxon name and optionally other arguments.

E.g. `path/to/php phylodiv.php Hexactinellida 10 15 15 path/to/clustalw2 path/to/paup`

List of command line arguments:
1. Taxonomic name of the group to analyze.
  * At phylum, order, class, family, subfamily or genus level, or species binomial. E.g. `Lachninae`, E.g. `Pandion%20haliaetus`
  * If there are spaces, enclose the argument in quotes `" "` or replace spaces with URI encoding character `%20`
2. Optionally, the number of subsamples to take from sequences obtained. 
  * Default = 20.

Optionally, the size of the grid squares to separate sequences by:

3. Latitude (width) of grid squares
  * Default = 30
4. Longitude (height) of grid squares
  * Default = 30
5. Optionally, the path to the Clustal executable.
  * Default = `\"Program Files (x86)"\ClustalW2\clustalw2` on windows, and `/usr/local/bin/clustalw2` otherwise
6. Optionally, the path to the PAUP executable.
  * Default = `%appdata%\PAUP4\paup4` on windows, and `/usr/local/bin/paup` otherwise
