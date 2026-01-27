<?php

namespace Lyre\File\Actions;

class NamesGenerator
{
    public static array $adjectives = [
        "anointed",
        "blessed",
        "chosen",
        "compassionate",
        "content",
        "courageous",
        "devout",
        "diligent",
        "faithful",
        "forgiving",
        "gentle",
        "glorious",
        "gracious",
        "holy",
        "humble",
        "just",
        "kind",
        "longsuffering",
        "loving",
        "merciful",
        "mighty",
        "obedient",
        "patient",
        "peaceful",
        "praiseworthy",
        "pure",
        "redeemed",
        "repentant",
        "reverent",
        "righteous",
        "sacrificial",
        "sanctified",
        "selfless",
        "steadfast",
        "strong",
        "thankful",
        "trusting",
        "truthful",
        "upright",
        "valiant",
        "virtuous",
        "watchful",
        "wise",
        "zealous",
    ];

    public static array $people = [
        'Abihud',
        'Achim',
        'Adbeel',
        'Addi',
        'Aminadab',
        'Amminadab',
        'Aram',
        'Arni',
        'Azor',
        'Basemath',
        'Becher',
        'Buz',
        'Chesed',
        'Carmi',
        'Eliezer',
        'Elmadam',
        'Eleazar',
        'Eliehoenai',
        'Eliada',
        'Eliakim',
        'Elmodam',
        'Enosh',
        'Eri',
        'Esli',
        'Ethnan',
        'Gad',
        'Gamul',
        'Guni',
        'Hazarmaveth',
        'Heber',
        'Helah',
        'Hezron',
        'Hodaviah',
        'Ishbak',
        'Ishhod',
        'Ishmaiah',
        'Ishuah',
        'Jahleel',
        'Jakim',
        'Jared',
        'Jecoliah',
        'Jeconiah',
        'Jediael',
        'Jeiel',
        'Jephunneh',
        'Jerah',
        'Jerioth',
        'Jezaniah',
        'Joanan',
        'Jokshan',
        'Joktan',
        'Kemuel',
        'Kezia',
        'Lehabim',
        'Maaz',
        'Maadai',
        'Magog',
        'Malchi',
        'Malchiel',
        'Malchiram',
        'Matthat',
        'Melchi',
        'Meshech',
        'Mibzar',
        'Midian',
        'Mizpar',
        'Mushi',
        'Nahor',
        'Necho',
        'Nephish',
        'Neriah',
        'Obil',
        'Pallu',
        'Peleg',
        'Pekahiah',
        'Rephael',
        'Reu',
        'Reuel',
        'Riphath',
        'Salu',
        'Seba',
        'Sered',
        'Shaashgaz',
        'Shallum',
        'Shelah',
        'Shelanites',
        'Shemaiah',
        'Shobal',
        'Shuni',
        'Tahath',
        'Tidal',
        'Tola',
        'Tubal',
        'Ucal',
        'Uzziel',
        'Zebulun',
        'Zerahiah',
        'Zibiah',
        'Zillah',
        'Ziphion',
        'Zuriel'
    ];

    /**
     * Invoke
     *
     * @param array $params
     * @return String $name
     */
    public function __invoke(array $params = []): string
    {
        return static::generate($params);
    }

    /**
     * Generate Docker-like random names to use in your applications.
     *
     * @param  array $params
     * @return String $name
     */
    public static function generate(array $params = []): string
    {
        $defaults = [
            "delimiter" => "-",
            "token" => 0,
            "chars"  => "0123456789",
        ];

        $params = array_merge($defaults, $params);

        $adjective = self::$adjectives[mt_rand(0, count(self::$adjectives) - 1)];
        $person = self::$people[mt_rand(0, count(self::$people) - 1)];

        $token = "";
        for ($i = 0; $i < $params["token"]; $i++) {
            $token .= $params["chars"][mt_rand(0, strlen($params["chars"]) - 1)];
        }

        $sections = [$adjective, $person, $token];

        return implode($params["delimiter"], array_filter($sections));
    }
}
