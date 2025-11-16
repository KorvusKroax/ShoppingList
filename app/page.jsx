"use client";
import { useState, useEffect } from "react";

export default function Page() {
  const API = "http://localhost/ShoppingList/backend/";

  const [items, setItems] = useState([]);
  const [text, setText] = useState("");

  // --- ITEMS BETÖLTÉSE ---
  async function loadItems() {
    const res = await fetch(API + "getItems.php");
    const data = await res.json();
    setItems(data);
  }

  // --- ÚJ TÉTEL HOZZÁADÁSA ---
  async function addItem() {
    if (!text.trim()) return;
    await fetch(API + "addItem.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name: text })
    });
    setText("");
    loadItems();
  }

  // --- PIPÁLÁS ---
  async function toggleItem(id, checked) {
    await fetch(API + "toggleItem.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, checked })
    });
    loadItems();
  }

  // --- TÖRLÉS ---
  async function deleteItem(id) {
    await fetch(API + "deleteItem.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    });
    loadItems();
  }

  useEffect(() => {
    loadItems();
  }, []);

  return (
    <div className="min-h-screen bg-gray-100 px-4 py-6 flex justify-center">
      <div className="w-full max-w-md bg-white shadow-xl rounded-2xl p-6">

        <h1 className="text-2xl font-bold mb-4 text-center">
          🛒 Bevásárlólista
        </h1>

        {/* Input + gomb */}
        <form
          onSubmit={e => {
            e.preventDefault(); // ne töltse újra az oldalt
            addItem();
          }}
          className="flex gap-2 mb-5"
        >
          <input
            value={text}
            onChange={e => setText(e.target.value)}
            placeholder="Új tétel..."
            className="flex-grow border rounded-xl px-3 py-2 shadow-sm focus:ring-2 focus:ring-blue-400 outline-none"
          />
          <button
            type="submit"
            className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl shadow"
          >
            &#10010;
          </button>
        </form>

        {/* Lista */}
        <ul className="space-y-3">
          {items.map(i => (
            <li
              key={i.id}
              className="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-xl shadow-sm"
            >
              <div className="flex items-center gap-3">
                <input
                  type="checkbox"
                  checked={i.checked == 1}
                  onChange={() =>
                    toggleItem(i.id, i.checked == 1 ? 0 : 1)
                  }
                  className="w-5 h-5"
                />

                <span
                  className={
                    "text-lg " +
                    (i.checked == 1 ? "line-through text-gray-400" : "")
                  }
                >
                  {i.name}
                </span>
              </div>

              <button
                onClick={() => deleteItem(i.id)}
                className="text-red-500 hover:text-red-700 text-xl"
              >
                ❌
              </button>
            </li>
          ))}
        </ul>

      </div>
    </div>
  );
}
