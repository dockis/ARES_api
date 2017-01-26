## Aplikace ARES api

Jednoduchá aplikace implementující ARES api.

## Nastavení projektu

Nastavení pro připojení k databázi se provede skriptem install.php

Požadované údaje:
- host
- database
- user
- password

Skript vytvoří novou databázi a v ní tabulku ares.
Jestliže databáze již existuje, akce skončí s upozorněním na nemožnost vytvoření.

Zadané údaje se uloží do souboru config/config.php

## Popis aplikace

Aplikace obsahuje 2 vyhledávací pole
- IČO
- název firmy

Vyhledávají se jen aktivní subjekty.

Vyhledávat lze v čestině, tj. s diakritikou.

Kontrola zadaných dat na úrovni jS a následně i PHP
Provádí se též kontrolní součet IČO.

Uložení do databáze probíhá při zobrazení detailu nebo při vyhledání subjektu
podle IČO.

Update záznamu se provede je-li starší více než jeden měsíc.

Aplikace využívá AJAX dotazů a sortování výpisu vícero subjektů probíhá bez dotazů na server.