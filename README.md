# Aplikacion Web për Rezervimin e Tureve

## Përshkrimi
Ky projekt është një aplikacion web i zhvilluar në PHP që mundëson shfletimin dhe rezervimin e tureve turistike. Sistemi është ndërtuar me strukturë reale të faqeve dhe logjikë funksionale, duke përdorur të dhëna të simuluara (pa databazë në këtë fazë).

Aplikacioni mbështet dy role përdoruesish:
- Klient
- Admin



## Funksionalitetet 
- Login dhe logout me të dhëna statike (hardcoded)
- Menaxhim i sesioneve për ruajtjen e gjendjes së përdoruesit
- Dallim i qasjeve sipas rolit (klient/admin)
- Shfletim i tureve të disponueshme
- Rezervim i tureve (i simuluar)
- Dashboard për shikimin e rezervimeve
- Panel administratori për menaxhimin e tureve (shtim/heqje – i simuluar)
- Përdorimi i cookies për personalizim
- Validim i inputeve me RegEx (p.sh. email)



## Teknologjitë e përdorura
- PHP
- HTML
- CSS




## Konceptet e implementuara
- Variabla, kushte dhe cikle
- Funksione
- Vargje (arrays): numeric, associative dhe multidimensional
- Programim i orientuar në objekte (OOP)
  - Klasa dhe objekte
  - Enkapsulim (get/set)
  - Trashëgimi
- Sessions dhe Cookies
- Validim me shprehje të rregullta (RegEx)



## Struktura e projektit
- `index.php` – Faqja kryesore
- `login.php` / `logout.php` – Autentifikimi
- `dashboard.php` – Paneli i përdoruesit/adminit
- `tours.php` / `tour.php` – Shfaqja e tureve
- `booking.php` – Logjika e rezervimit
- `admin_tours.php` – Menaxhimi i tureve nga admini
- `functions.php` – Funksione ndihmëse
- `config.php` – Konfigurimi
- `style.css` – Dizajnimi



## Ekzekutimi i projektit
1. Instaloni XAMPP ose WAMP
2. Vendosni projektin në folderin `htdocs`
3. Startoni Apache
4. Hapni në browser






## Gjithashtu
Ky projekt përfaqëson Fazën I dhe nuk përfshin lidhje me databazë. Të gjitha të dhënat janë të simuluara. Në Fazën II do të implementohet lidhja me databazë dhe funksionalitete të avancuara.

