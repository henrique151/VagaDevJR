document.addEventListener("DOMContentLoaded", function () {
    // Variáveis globais
    window.parcelasEdicao = [];
    let valorTotalVenda = 0;

    // Elementos do DOM
    const editarTipoPagamento = document.getElementById(
        "editar_tipo_pagamento"
    );
    const editarFormaPagamento = document.getElementById(
        "editar_forma_pagamento"
    );
    const boxParcelas = document.getElementById("editar_boxParcelas");
    const qtdParcelas = document.getElementById("editar_qtdParcelas");
    const vencimentoInicial = document.getElementById(
        "editar_vencimentoInicial"
    );
    const btnGerarParcelas = document.getElementById("btnEditarGerarParcelas");
    const formPrincipal = document.getElementById("formEditarVendaPrincipal");
    const listaParcelas = document.getElementById("editar_listaParcelas");
    const inputParcelasData = document.getElementById("parcelas_data");
    const avisoMensagem = document.getElementById("avisoMensagem");

    inicializarParcelasExistentes();

    if (editarFormaPagamento) {
        editarFormaPagamento.addEventListener("change", function () {
            toggleParcelasBox();
            validarCombinacaoPagamento();
        });
    }

    if (editarTipoPagamento) {
        editarTipoPagamento.addEventListener(
            "change",
            validarCombinacaoPagamento
        );
    }

    if (btnGerarParcelas) {
        btnGerarParcelas.addEventListener("click", gerarParcelas);
    }

    if (formPrincipal) {
        formPrincipal.addEventListener("submit", function (e) {
            e.preventDefault();
            try {
                prepararParcelasParaEnvio();
                this.submit();
            } catch (error) {
                console.error("Erro ao preparar parcelas:", error);
            }
        });
    }

    function inicializarParcelasExistentes() {
        const rows = document.querySelectorAll("#editar_listaParcelas tr");

        if (rows.length > 1) {
            parcelasEdicao = [];

            const primeiraLinha = rows[0];
            const temMensagemVazia =
                primeiraLinha.querySelector('td[colspan="3"]');

            if (!temMensagemVazia) {
                for (let i = 1; i < rows.length; i++) {
                    const row = rows[i];
                    const inputs = row.querySelectorAll("input");

                    if (inputs.length >= 2) {
                        parcelasEdicao.push({
                            numero: i,
                            vencimento: inputs[0].value,
                            valor: parseFloat(inputs[1].value) || 0,
                            tipo_pagamento:
                                editarTipoPagamento?.value || "cartao_credito",
                        });
                    }
                }
                console.log(
                    "Parcelas existentes inicializadas:",
                    parcelasEdicao
                );
            }
        }

        valorTotalVenda = calcularValorTotalAtual();
        console.log("Valor total da venda calculado:", valorTotalVenda);
    }

    function calcularValorTotalAtual() {
        let total = 0;
        const precos = document.querySelectorAll(
            'input[name$="[preco_unitario]"]'
        );
        const quantidades = document.querySelectorAll(
            'input[name$="[quantidade]"]'
        );

        if (precos.length > 0 && precos.length === quantidades.length) {
            for (let i = 0; i < precos.length; i++) {
                const preco = parseFloat(precos[i].value) || 0;
                const quantidade = parseInt(quantidades[i].value) || 0;
                total += preco * quantidade;
            }
        }

        return total;
    }

    function toggleParcelasBox() {
        boxParcelas.style.display =
            editarFormaPagamento.value === "parcelado" ? "block" : "none";
    }

    function validarCombinacaoPagamento() {
        const tipoPagamento = editarTipoPagamento.value;
        const formaPagamento = editarFormaPagamento.value;

        avisoMensagem.style.display = "none";

        if (tipoPagamento === "dinheiro" && formaPagamento === "parcelado") {
            avisoMensagem.textContent =
                "Pagamento em dinheiro não pode ser parcelado.";
            avisoMensagem.style.display = "block";
            editarFormaPagamento.value = "avista";
            toggleParcelasBox();
        } else if (
            tipoPagamento === "boleto" &&
            formaPagamento === "parcelado"
        ) {
            avisoMensagem.textContent =
                "Recomendamos configurar cada boleto individualmente.";
            avisoMensagem.style.display = "block";
        }
    }

    function gerarParcelas() {
        const quantidade = parseInt(qtdParcelas.value);
        const dataInicial = vencimentoInicial.value;

        if (!quantidade || quantidade <= 0) {
            alert("Por favor, informe uma quantidade válida de parcelas.");
            return;
        }

        if (!dataInicial) {
            alert("Por favor, informe a data do primeiro vencimento.");
            return;
        }

        valorTotalVenda = calcularValorTotalAtual();
        const valorParcela = (valorTotalVenda / quantidade).toFixed(2);
        const valorParcelaFloat = parseFloat(valorParcela);
        const valorTotalParcelas = valorParcelaFloat * quantidade;
        const diferenca = valorTotalVenda - valorTotalParcelas;

        parcelasEdicao = [];

        for (let i = 1; i <= quantidade; i++) {
            const dataVencimento = calcularDataVencimento(dataInicial, i - 1);
            let valorFinal = valorParcelaFloat;
            if (i === quantidade && Math.abs(diferenca) > 0.01) {
                valorFinal = valorParcelaFloat + diferenca;
            }

            parcelasEdicao.push({
                numero: i,
                vencimento: dataVencimento,
                valor: valorFinal,
                tipo_pagamento: editarTipoPagamento?.value || "cartao_credito",
            });
        }

        atualizarTabelaParcelas();
    }

    function calcularDataVencimento(dataInicial, mesesAdicionais) {
        const data = new Date(dataInicial);
        data.setMonth(data.getMonth() + mesesAdicionais);
        const ano = data.getFullYear();
        const mes = String(data.getMonth() + 1).padStart(2, "0");
        const dia = String(data.getDate()).padStart(2, "0");
        return `${ano}-${mes}-${dia}`;
    }

    function atualizarTabelaParcelas() {
        let html = "";

        if (parcelasEdicao.length === 0) {
            html =
                '<tr><td colspan="3" class="text-center">Nenhuma parcela cadastrada.</td></tr>';
        } else {
            html = `
            <tr>
                <th>#</th>
                <th>Data de Vencimento</th>
                <th>Valor</th>
            </tr>
            `;

            parcelasEdicao.forEach((parcela, index) => {
                html += `
                <tr>
                    <td>
                        ${parcela.numero}
                        <input type="hidden" name="parcelas[${index}][numero]" value="${
                    parcela.numero
                }">
                    </td>
                    <td>
                        <input 
                            type="date" 
                            class="form-control" 
                            name="parcelas[${index}][vencimento]"
                            value="${parcela.vencimento}"
                            onchange="parcelasEdicao[${index}].vencimento = this.value;"
                        >
                    </td>
                    <td>
                        <input 
                            type="number" 
                            class="form-control" 
                            name="parcelas[${index}][valor]"
                            step="0.01" 
                            value="${parcela.valor.toFixed(2)}"
                            onchange="parcelasEdicao[${index}].valor = parseFloat(this.value);"
                        >
                        <input 
                            type="hidden" 
                            name="parcelas[${index}][tipo_pagamento]"
                            value="${parcela.tipo_pagamento}"
                            onchange="parcelasEdicao[${index}].tipo_pagamento = this.value;">
                    </td>
                </tr>
                `;
            });
        }

        listaParcelas.innerHTML = html;
    }

    function prepararParcelasParaEnvio() {
        if (
            parcelasEdicao.length > 0 &&
            editarFormaPagamento.value === "parcelado"
        ) {
            const parcelasValidadas = parcelasEdicao.map((parcela) => ({
                numero: parcela.numero,
                vencimento: parcela.vencimento || "",
                valor: parseFloat(parcela.valor) || 0,
                tipo_pagamento:
                    parcela.tipo_pagamento ||
                    editarTipoPagamento?.value ||
                    "cartao_credito",
            }));

            const todasValidas = parcelasValidadas.every(
                (p) =>
                    p.vencimento &&
                    p.vencimento.trim() !== "" &&
                    p.valor &&
                    p.valor > 0
            );

            if (!todasValidas) {
                alert(
                    "Verifique se todas as parcelas têm vencimento e valor válido."
                );
                throw new Error("Dados de parcelas inválidos");
            }

            inputParcelasData.value = JSON.stringify(parcelasValidadas);
        } else if (editarFormaPagamento.value === "avista") {
            inputParcelasData.value = JSON.stringify([
                {
                    numero: 1,
                    vencimento: new Date().toISOString().split("T")[0],
                    valor: calcularValorTotalAtual(),
                    tipo_pagamento:
                        editarTipoPagamento?.value || "cartao_credito",
                },
            ]);
        } else {
            inputParcelasData.value = "";
        }
    }
});
