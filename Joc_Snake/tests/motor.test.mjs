// Proves del motor de Snake. Execució:  node --test tests/
import test from "node:test";
import assert from "node:assert/strict";
import { MotorSnake } from "../motor.js";

const VEL = { inicialMs: 150, minimMs: 60, reduccioMs: 8, pomesPerNivell: 3 };
const nou = (extra = {}) => new MotorSnake({ columnes: 10, files: 10, velocitat: VEL, ...extra });
const jugar = (m) => { m.iniciar(); return m; };

test("la serp comença amb 3 segments al centre i en estat preparat", () => {
  const m = nou();
  assert.equal(m.serp.length, 3);
  assert.equal(m.estat, "preparat");
  assert.deepEqual(m.serp[0], { x: 5, y: 5 });
});

test("no es mou si no s'ha iniciat o està en pausa", () => {
  const m = nou();
  assert.equal(m.pas().tipus, "cap");
  jugar(m).pausar();
  assert.equal(m.pas().tipus, "cap");
});

test("avança una cel·la per pas i conserva la longitud", () => {
  const m = jugar(nou());
  m.poma = { x: 0, y: 0 };
  m.pas();
  assert.deepEqual(m.serp[0], { x: 6, y: 5 });
  assert.equal(m.serp.length, 3);
});

test("no permet invertir la direcció (180º)", () => {
  const m = jugar(nou());
  assert.equal(m.canviarDireccio("esquerra"), false);
  m.pas();
  assert.equal(m.direccio, "dreta");
});

test("dues pulsacions dins d'un tick no poden provocar un gir de 180º", () => {
  const m = jugar(nou());
  m.poma = { x: 0, y: 0 };
  assert.equal(m.canviarDireccio("amunt"), true);
  assert.equal(m.canviarDireccio("esquerra"), true);
  assert.equal(m.canviarDireccio("dreta"), false);
  m.pas(); m.pas();
  assert.equal(m.estat, "jugant");
  assert.equal(m.direccio, "esquerra");
});

test("la cua de girs és limitada a 2", () => {
  const m = jugar(nou());
  assert.equal(m.canviarDireccio("amunt"), true);
  assert.equal(m.canviarDireccio("esquerra"), true);
  assert.equal(m.canviarDireccio("avall"), false);
});

test("menjar una poma fa créixer la serp i suma punts", () => {
  const m = jugar(nou());
  m.poma = { x: 6, y: 5 };
  const ev = m.pas();
  assert.equal(ev.tipus, "poma");
  assert.equal(m.serp.length, 4);
  assert.equal(m.puntuacio, 1);
  assert.equal(m.pomes, 1);
});

test("la nova poma mai apareix dins de la serp (1000 proves)", () => {
  for (let i = 0; i < 1000; i++) {
    const m = jugar(nou());
    m.poma = { x: 6, y: 5 };
    m.pas();
    assert.ok(!m.serp.some((s) => s.x === m.poma.x && s.y === m.poma.y));
  }
});

test("xoc contra cada paret acaba la partida", () => {
  const casos = [
    { dir: "dreta", gir: null },
    { dir: "dreta", gir: "amunt" },
    { dir: "dreta", gir: "avall" },
    { dir: "esquerra", gir: null },
  ];
  for (const { dir, gir } of casos) {
    const m = jugar(nou({ direccioInicial: dir }));
    m.poma = { x: 0, y: 0 };
    if (gir) m.canviarDireccio(gir);
    let ev;
    for (let i = 0; i < 30 && m.estat === "jugant"; i++) ev = m.pas();
    assert.equal(m.estat, "acabat", `${dir}/${gir}`);
    assert.equal(ev.causa, "paret", `${dir}/${gir}`);
  }
});

test("xoc contra el propi cos acaba la partida", () => {
  const m = jugar(nou({ longitudInicial: 5 }));
  m.poma = { x: 0, y: 0 };
  m.canviarDireccio("avall"); m.pas();
  m.canviarDireccio("esquerra"); m.pas();
  m.canviarDireccio("amunt");
  const ev = m.pas();
  assert.equal(ev.tipus, "fi");
  assert.equal(ev.causa, "cos");
});

test("es pot entrar a la cel·la que deixa la cua (no és xoc)", () => {
  const m = jugar(nou({ longitudInicial: 4 }));
  m.poma = { x: 0, y: 0 };
  m.serp = [{ x: 4, y: 4 }, { x: 4, y: 5 }, { x: 5, y: 5 }, { x: 5, y: 4 }];
  m.direccio = "esquerra";
  m.canviarDireccio("amunt");
  // el cap (4,4) va cap amunt: lliure; comprovem el cas de la cua amb un cercle de 2x2
  m.serp = [{ x: 5, y: 4 }, { x: 4, y: 4 }, { x: 4, y: 5 }, { x: 5, y: 5 }];
  m.direccio = "avall";
  m.cua = [];
  m.pas(); // el cap entra a (5,5), on hi ha la cua que s'acaba de moure
  assert.equal(m.estat, "jugant");
});

test("la velocitat augmenta amb el nivell i té un mínim", () => {
  const m = nou();
  assert.equal(m.intervalMs, 150);
  m.pomes = 3; assert.equal(m.nivell, 2); assert.equal(m.intervalMs, 142);
  m.pomes = 300; assert.equal(m.intervalMs, 60);
});

test("reiniciar restableix partida, puntuació i estat", () => {
  const m = jugar(nou());
  m.poma = { x: 6, y: 5 }; m.pas();
  m.reiniciar();
  assert.equal(m.puntuacio, 0);
  assert.equal(m.serp.length, 3);
  assert.equal(m.estat, "preparat");
  assert.equal(m.causaFi, null);
});

test("quan el tauler s'omple la partida acaba amb 'completat'", () => {
  const m = jugar(new MotorSnake({ columnes: 4, files: 1, longitudInicial: 3, velocitat: VEL }));
  m.serp = [{ x: 2, y: 0 }, { x: 1, y: 0 }, { x: 0, y: 0 }];
  m.direccio = "dreta";
  m.poma = { x: 3, y: 0 };
  const ev = m.pas();
  assert.equal(ev.tipus, "fi");
  assert.equal(ev.causa, "completat");
});
