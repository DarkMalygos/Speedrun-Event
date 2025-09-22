# Speedrun Event

Ez a projekt egy **Speedrun esemény kezelő alkalmazás**, amelyben a felhasználók egy weboldalon keresztül tudnak jelentkezni egy meghatározott férőhelyes eseményre.
A rendszer célja, hogy kezelje a jelentkezéseket, a várólistát és a lemondásokat egy átlátható logika mentén.

## Technológiai stack
- **Backend**: Symfony Framework (REST API)
- **Adatbázis**: MySQL
- **Frontend**: Twig + Bootstrap
- **Fejlesztői cél**: Gyors protótípus, jól struktúrált REST API végpontokkal és egyszerű, letisztult felhasználói felülettel

## Funkcionalitás
- Felhasználói jelentkezés az eseményre
- Férőhely kezelés és várólista logika
- Lemondás és automatikus várólista-feloldás
- REST API végpoontok
- Bootstrap-alapú felhasználói felület Twig template-ekkel

---

### Alkalmazás futtatása saját gépen
- git clone https://github.com/DarkMalygos/Speedrun-Event.git
- cd Speedrun-Event

## Függőségek telepítése (ha kell)
- composer install
- composer require symfony/orm-pack
- composer require --dev symfony/maker-bundle

## Adatbázis konfigurálása
- A .env file-ban található "DATABASE_URL"-nek adja meg az adatbázis url-jét.
- Adatbázis létrehozása: php bin/console doctrine:database:create (IDE terminal-ban is lehet)
- Az adatbázisba SQL lekérdezéseket ne futtasson, a migráció mindent létrehoz Önnek!

## Migrációk futtatása a létrehozott adatbázison
- php bin/console doctrine:migrations:migrate

## Szerver elindítása:
- symfony server:start

Az alkalmazást a terminal-ban látható linken keresztül elérheti.
