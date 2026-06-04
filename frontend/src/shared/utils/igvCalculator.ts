export const IGV_RATE = 0.18;

export interface IgvLineResult {
  total_linea: number;
  gravada: number;
  igv: number;
  total: number;
}

export function calcularLineaIgv(
  cantidad: number,
  precioUnitario: number,
  descuentoLinea = 0,
): IgvLineResult {
  const totalLinea = round(cantidad * precioUnitario - descuentoLinea, 2);
  const gravada = round(totalLinea / (1 + IGV_RATE), 2);
  const igv = round(gravada * IGV_RATE, 2);
  return {
    total_linea: totalLinea,
    gravada: gravada,
    igv: igv,
    total: round(gravada + igv, 2),
  };
}

export interface IgvTotalsResult {
  subtotal: number;
  igv: number;
  total: number;
}

export function calcularTotalesIgv(
  lines: Array<{ cantidad: number; precio_unitario: number; descuento_linea?: number }>,
): IgvTotalsResult {
  let subtotal = 0;
  let igv = 0;
  let total = 0;
  for (const line of lines) {
    const r = calcularLineaIgv(line.cantidad, line.precio_unitario, line.descuento_linea ?? 0);
    subtotal += r.gravada;
    igv += r.igv;
    total += r.total_linea;
  }
  return {
    subtotal: round(subtotal, 2),
    igv: round(igv, 2),
    total: round(total, 2),
  };
}

function round(n: number, decimals: number): number {
  const factor = 10 ** decimals;
  return Math.round(n * factor) / factor;
}
