'use client';

import { useAuth } from '@/context/AuthContext';

export default function DashboardPage() {
  // A useAuth hook segítségével lekérjük az állapotot és a logout funkciót
  const { jwtToken, logout } = useAuth();

  // Megjegyzés: A valós jogosultság ellenőrzés (AuthGuard) a következő lépésben következik!

  return (
    <div style={{ maxWidth: '800px', margin: '50px auto' }}>
      <h1>Dashboard (Bevásárlólisták) 🛒</h1>
      <p style={{ color: 'green' }}>Sikeresen bejelentkeztél! Ez egy védett terület.</p>

      <p>A jelenlegi JWT token (elrejteni!):</p>
      <textarea
        readOnly
        value={jwtToken || ''}
        style={{ width: '100%', height: '100px', fontSize: '10px', border: '1px solid #ddd' }}
      />

      <button
        onClick={() => logout(false)}
        style={{ padding: '10px 20px', backgroundColor: 'red', color: 'white', border: 'none', borderRadius: '4px', marginTop: '20px' }}
      >
        Kijelentkezés
      </button>
    </div>
  );
}
