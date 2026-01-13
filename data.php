<?php
// data.php : contient la "base de données" du test (sans SQL)
// -> Tu peux ajouter / modifier des questions ici sans toucher au reste du site.

return [
    "meta" => [
        "title" => "Test de personnalité",
        "subtitle" => "Réponds honnêtement, on juge pas (un peu).",
        "version" => "1.0"
    ],

    // 4 profils
    "profiles" => [
        "A" => [
            "name" => "Le Stratège Calme",
            "short" => "Réfléchi, posé, tu gères en silence.",
            "desc" => "Tu prends le temps d’analyser avant d’agir. Tu préfères les plans solides plutôt que l’improvisation. Quand ça part en vrille, toi t’es le seul qui respire encore normalement.",
            "tips" => [
                "Prends parfois des décisions plus vite : ton intuition est meilleure que tu crois.",
                "Évite de trop ruminer : fais un mini plan et GO.",
                "Entoure-toi de gens action : ça te boost."
            ]
        ],
        "B" => [
            "name" => "L’Énergie Sociale",
            "short" => "Sociable, rapide, tu fais bouger les trucs.",
            "desc" => "Tu passes à l’action facilement et t’aimes quand ça avance. Tu te nourris des échanges et du mouvement. Si y’a une embrouille, t’es déjà en train de trouver une solution (ou de rire).",
            "tips" => [
                "Pense à faire des pauses : ton cerveau chauffe parfois sans ventilateur.",
                "Écoute plus longtemps avant de trancher : tu seras encore plus efficace.",
                "Canalise ton énergie sur 1-2 objectifs au lieu de 12."
            ]
        ],
        "C" => [
            "name" => "Le Créatif Curieux",
            "short" => "Imaginatif, plein d’idées, tu vois les détails que personne voit.",
            "desc" => "Tu captes vite les ambiances, tu adores explorer, apprendre, tester. Ton cerveau te balance des idées à 3h du mat comme si c’était normal. Tu pourrais inventer un monde entier (et tu l’as déjà fait).",
            "tips" => [
                "Note tes idées : sinon elles s’évaporent.",
                "Fais des petits projets courts : meilleur moyen d’aller au bout.",
                "Garde un cadre simple : créativité + discipline = cheat code."
            ]
        ],
        "D" => [
            "name" => "Le Logique Analytique",
            "short" => "Structuré, rationnel, tu veux du concret.",
            "desc" => "Tu aimes comprendre comment ça marche réellement. Tu poses des questions précises, tu aimes les preuves, les étapes, les systèmes. On te donne un flou artistique : tu demandes ‘ok mais ça marche comment ?’.",
            "tips" => [
                "Accepte que parfois ‘assez bien’ c’est suffisant.",
                "Pense à communiquer tes idées simplement (tout le monde n’est pas dans ton cerveau).",
                "Ajoute un peu d’intuition à ta logique : ça rend encore plus fort."
            ]
        ],
    ],

    // 10 questions (facile d'en ajouter)
    // Chaque question a 4 choix -> A/B/C/D
    "questions" => [
        [
            "id" => "q1",
            "title" => "Quand tu démarres un projet :",
            "choices" => [
                "A" => "Je planifie avant de faire quoi que ce soit.",
                "B" => "Je commence direct et j’ajuste en route.",
                "C" => "Je pars en exploration d’idées et d’inspiration.",
                "D" => "Je cherche d’abord les règles, contraintes, et la meilleure méthode."
            ]
        ],
        [
            "id" => "q2",
            "title" => "Dans un groupe, tu es plutôt :",
            "choices" => [
                "A" => "Celui qui calme le jeu et organise tranquillement.",
                "B" => "Celui qui motive et fait avancer l’équipe.",
                "C" => "Celui qui propose des idées originales.",
                "D" => "Celui qui analyse et repère les incohérences."
            ]
        ],
        [
            "id" => "q3",
            "title" => "Quand tu dois choisir rapidement :",
            "choices" => [
                "A" => "Je prends 30 secondes pour réfléchir, même si on me presse.",
                "B" => "Je décide vite, et on verra après.",
                "C" => "Je choisis ce qui me semble le plus ‘fun’ ou intéressant.",
                "D" => "Je choisis la solution la plus logique et stable."
            ]
        ],
        [
            "id" => "q4",
            "title" => "Ton environnement idéal pour bosser :",
            "choices" => [
                "A" => "Calme, propre, sans bruit.",
                "B" => "Ambiance vivante, musique, ça bouge.",
                "C" => "Endroit inspirant, posters, idées partout.",
                "D" => "Organisation béton, outils bien rangés, setup carré."
            ]
        ],
        [
            "id" => "q5",
            "title" => "Quand tu apprends un truc nouveau :",
            "choices" => [
                "A" => "J’apprends étape par étape, sans brûler les étapes.",
                "B" => "Je teste direct, je comprends en faisant.",
                "C" => "Je fais des liens avec d’autres trucs, j’imagine des exemples.",
                "D" => "Je veux comprendre le fonctionnement exact et les détails."
            ]
        ],
        [
            "id" => "q6",
            "title" => "Face à un problème technique :",
            "choices" => [
                "A" => "Je garde mon calme et je cherche méthodiquement.",
                "B" => "Je tente plusieurs trucs vite fait jusqu’à ce que ça marche.",
                "C" => "Je trouve une solution ‘différente’ ou une astuce.",
                "D" => "Je lis l’erreur, je trace la cause, je corrige à la racine."
            ]
        ],
        [
            "id" => "q7",
            "title" => "Quand on te critique :",
            "choices" => [
                "A" => "Je réfléchis avant de répondre, j’analyse.",
                "B" => "Je réponds du tac au tac (parfois trop).",
                "C" => "Je le prends à cœur, puis j’en fais un truc créatif.",
                "D" => "Je demande des faits précis : ‘qu’est-ce qui va pas exactement ?’"
            ]
        ],
        [
            "id" => "q8",
            "title" => "Ton style de décision au quotidien :",
            "choices" => [
                "A" => "Je préfère les décisions sûres et cohérentes.",
                "B" => "Je préfère les décisions rapides et efficaces.",
                "C" => "Je préfère les décisions qui ouvrent des possibilités.",
                "D" => "Je préfère les décisions basées sur la logique et les infos."
            ]
        ],
        [
            "id" => "q9",
            "title" => "Ce qui te fatigue le plus :",
            "choices" => [
                "A" => "Le chaos et le stress.",
                "B" => "La lenteur et l’inaction.",
                "C" => "La routine et le manque de nouveauté.",
                "D" => "Le flou et le manque d’explications."
            ]
        ],
        [
            "id" => "q10",
            "title" => "Tu te reconnais le plus dans :",
            "choices" => [
                "A" => "La patience.",
                "B" => "L’énergie.",
                "C" => "L’imagination.",
                "D" => "La logique."
            ]
        ],
    ]
];
