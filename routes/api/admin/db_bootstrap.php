<?php
db_drop_and_create_table("users", 
"uid INT AUTO_INCREMENT PRIMARY KEY",
"username VARCHAR(25) NOT NULL UNIQUE",
"role ENUM('USER', 'ADMIN') NOT NULL",
"password VARCHAR(255) NOT NULL", // nunca vai passar disso, e se não chegar não vira desperdício
"created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
);

db_drop_and_create_table("sites",
"sid INT AUTO_INCREMENT PRIMARY KEY",
"domain VARCHAR(255) UNIQUE", // tem que validar se bate com o domínio em sí do site
"owner INT NOT NULL", // uid do dono do site, pode ser um usuário admin ou normal
"created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
);

db_drop_and_create_table("slug", // post do site, é passado no script
"slid INT AUTO_INCREMENT PRIMARY KEY", // id do slug do site
"sid INT NOT NULL",
"slug VARCHAR(255) NOT NULL", // id do post do site
"created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
);

db_drop_and_create_table("comments",
"cid INT AUTO_INCREMENT PRIMARY KEY",
"slid INT NOT NULL",
"uid INT NOT NULL",
"body TEXT NOT NULL", // sanitizar, aceitar apenas texto
"created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
"edited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
);

db_drop_and_create_table("analytics_datapoint",
"sid INT NOT NULL", // agilizar query
"uid INT", // pode ser null pq o usuario pode entrar deslogado
"ip VARCHAR(40) NOT NULL", // tamanho máximo de um IPV6
"tag VARCHAR(20) NOT NULL", // visita? clique? dá pra usar pra testes AB
"is_mobile BOOL NOT NULL", // é um user agent do mobile?
"payload TEXT", // JSON
"created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
);

// TODO: demo, arrumar dps
db_execute(db_stmt(
    "insert into users 
    (username, role, password) 
    values ('root', 'ADMIN', ?)", 
    "s", 
    pw_create("123")
));

respond_sucess([
    "result" => "ok"
]);
?>