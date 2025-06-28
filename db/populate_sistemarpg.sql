
-- Inserir os sistemas de RPG iniciais na plataforma com fichas de personagem detalhadas.

-- Sistema 1: Apocalipse Zumbi
INSERT INTO `sistemas_rpg` (`nome_sistema`, `descricao`, `ficha_template_json`)
VALUES
    ('Apocalipse Zumbi', 'Um sistema focado na sobrevivência, gestão de recursos e nos horrores de um mundo dominado pelos mortos-vivos.', '{"nome_personagem":"","idade":0,"sexo":"","profissao_antes_do_surto":"","descricao_fisica":"","atributos":{"forca":0,"destreza":0,"intelecto":0,"carisma":0,"constituicao":0},"inventario":[],"vida":{"pontos_de_vida_atual":0,"pontos_de_vida_maximo":0,"ferimentos":{"leve":false,"grave":false,"critico":false}},"estado_mental":{"calmo":false,"tenso":false,"aterrorizado":false,"traumatizado":false},"pericias":[],"historico":{"maior_medo":"","perda_importante":"","mataria_para_sobreviver":""}}');

-- Sistema 2: Fantasia Medieval
INSERT INTO `sistemas_rpg` (`nome_sistema`, `descricao`, `ficha_template_json`)
VALUES
    ('Fantasia Medieval', 'Um sistema clássico de masmorras, dragões, magia e aventura em reinos distantes.', '{"nome_personagem":"","raca":"","classe":"","nivel":1,"alinhamento":"","idade":0,"sexo":"","altura":"","peso":"","divindade":"","historia_personal":"","atributos":{"forca":0,"destreza":0,"constituicao":0,"inteligencia":0,"sabedoria":0,"carisma":0},"modificadores":{"forca":0,"destreza":0,"constituicao":0,"inteligencia":0,"sabedoria":0,"carisma":0},"vida":{"pontos_de_vida_maximo":0,"pontos_de_vida_atual":0,"classe_armadura":0,"iniciativa":0,"deslocamento":"9m"},"habilidades_classe":[],"pericias":[],"proficiencias":{"armas":[],"armaduras":[],"ferramentas":[],"idiomas":[]},"magias":{"espacos_magia_por_nivel":{"nivel_1":0,"nivel_2":0,"nivel_3":0},"magias_conhecidas":[]},"equipamentos":[],"moedas":{"ouro":0,"prata":0,"cobre":0},"notas":""}');