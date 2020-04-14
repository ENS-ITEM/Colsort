Collection Sort (plugin for Omeka)
==================================

[Colsort] permet de gérer l'ordre des collections ; il utilise le classement
"collection parent/enfant" du plugin [Collection Tree] (qui doit être activé).

Celle-ci peut également inclure les titres des notices de chaque collection.
L’ordre des items est celui défini par le plugin [Item Order] (qui doit être
activé).


Installation
------------

Install first plugins [Collection Tree] (fork of [Daniel-KM]) and, if you want
to append the items in the tree of collections, [Item Order].

Then, uncompress files and rename plugin folder "Colsort".

Then install it like any other Omeka plugin and follow the config instructions.


Usage
-----

Le classement des collections se fait sur une page spécifique du tableau de
bord ("/admin/tri-collection") en indiquant un chiffre pour chaque collection.
L'affichage de la liste se fait sur une page publique "/arbre-collections".

Go to the page "/admin/tri-collections" via the menu link and fill some or all
fields with a priority value. The public collection tree will be automatically
displayed as an ordered tree at "/arbre-collections".


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [plugin issues] page on GitHub.


License
-------

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


Copyright
---------

* Copyright Vincent Buard, 2019 (see [Numerizen])
* Copyright Daniel Berthereau, 2020 (see [Daniel-KM] on GitHub)

Plugin réalisé avec le soutien du [projet ANR Transperse] pour la plate-forme
EMAN (Item, ENS-CNRS) par Vincent Buard ([Numerizen]), complété par Daniel Berthereau
pour la [Bibliothèque interuniversitaire de la Sorbonne].


[Colsort]: https://github.com/ENS-ITEM/Colsort
[Omeka]: https://omeka.org/classic
[Collection Tree]: https://github.com/Daniel-KM/Omeka-plugin-CollectionTree
[Item Order]: https://omeka.org/classic/plugins/ItemOrder
[plugin issues]: https://github.com/ENS-ITEM/Colsort/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[projet ANR Transperse]: https://transperse.hypotheses.org
[Bibliothèque interuniversitaire de la Sorbonne]: https://nubis.univ-paris1.fr
[Numerizen]: http://numerizen.com
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
