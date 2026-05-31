# Meu Caixa Nail

Sistema web de controle financeiro desenvolvido para auxiliar uma profissional autônoma da área de nail design na organização de suas entradas, despesas do negócio e gastos pessoais.

O objetivo do sistema é permitir uma visualização simples do resultado financeiro mensal, ajudando a profissional a entender quanto entrou, quanto foi gasto, qual foi o lucro real do negócio e quanto sobrou no mês.

## Sobre esta versão

Esta versão disponibilizada no GitHub não corresponde totalmente a versão que está no ar, pois alguns dados foram removidos ou substituídos por questões de segurança.

O arquivo `config/conexao.php`, por exemplo, não contém os dados reais de conexão com o banco de dados. 

Na versão publicada em produção, esse arquivo é configurado diretamente na hospedagem com os dados reais do banco.

## Funcionalidades

- Login de usuário
- Dashboard financeiro mensal
- Cadastro de entradas
- Cadastro de gastos do negócio
- Cadastro de gastos pessoais
- Listagem de lançamentos
- Edição e exclusão de lançamentos
- Filtro por mês e ano
- Busca por descrição ou categoria
- Resumo mensal por categoria
- Interface responsiva para uso no celular
- Configuração PWA para instalação na tela inicial do celular

## Tecnologias utilizadas

- PHP
- MySQL
- HTML
- CSS
- Bootstrap 5
- JavaScript
- PWA

## Banco de dados

O arquivo `database_exemplo.sql` contém a estrutura básica das tabelas utilizadas pelo sistema, além de categorias e formas de pagamento iniciais.

Por segurança, esse arquivo não contém usuários reais, senhas reais ou lançamentos financeiros reais.

## Observação de segurança

Este repositório foi preparado para fins acadêmicos e demonstrativos. Dados sensíveis, como credenciais de banco de dados, usuários reais e informações financeiras reais, não foram incluídos.

Para executar o sistema em uma hospedagem, é necessário configurar manualmente o arquivo `config/conexao.php` com os dados reais do banco de dados.
