# Configuração

Configurações iniciais para iniciar a aplicação.

## Credenciais

**Usuário Gerente**

```
E-mail: gerente@gmail.com
Senha: 123456
```

---
## Operação

 1. **Cadastro do cliente** 
	 - O gerente cadastra o cliente, é criado com saldo inicial **0**.
	 - O cliente cadastrado ainda não pode transferir nada.
 2. **Primeira operação: depósito**
	- Ele faz um depósito (ex.: via boleto, PIX).
	- É cadastrado uma transação de depósito no sistema.
	- O saldo do cliente é atualizado.
3. **Transferência**
	- Depois do depósito (ou se recebeu dinheiro de outro usuário), o cliente pode realizar uma transferência.
4. **Reversão**
	- O gerente pode realizar uma reversão
	- Cria uma nova transação "reversão"
	- Inverte os efeitos da transação original
	- Uma transação revertida não fica mais disponível para reversão