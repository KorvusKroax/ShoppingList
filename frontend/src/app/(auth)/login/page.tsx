'use client';

import React, { useState, FormEvent, useEffect } from 'react'; // 💡 Hozzáadva: useEffect
import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/navigation';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const { login, isAuthenticated } = useAuth();
  const router = useRouter();

  // 💡 JAVÍTÁS: Átirányítás a useEffect hook-ba helyezve!
  // Ez biztosítja, hogy az átirányítás a renderelési fázis után történjen meg.
  useEffect(() => {
    if (isAuthenticated) {
      // A replace() használata azért jó, mert lecseréli az aktuális útvonalat
      // a történelemben, így a felhasználó nem tud visszalépni a bejelentkezési oldalra.
      router.replace('/dashboard');
    }
  }, [isAuthenticated, router]); // Futtatás, ha isAuthenticated vagy router változik

  // A komponens most már rögtön renderelődik (nem return null a feltételen belül)
  // és az átirányítás a háttérben megtörténik.

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    try {
      // API hívás a context-ből
      await login(email, password);

    // Sikeres bejelentkezés után átirányítás
    // Megjegyzés: A 'login' beállítja az 'isAuthenticated' állapotot,
    // ami ezután elindítja a fenti useEffect-et. Bár itt is megtehető a push(),
    // az állapotvezérelt átirányítás a useEffect-ben tisztább.
    // router.push('/dashboard');

    // Ha a fenti useEffect megoldja az átirányítást, a router.push itt már redundáns.
    // Ha van okod arra, hogy ide is beszúrd, használhatod a push() helyett a replace()-t.
    router.replace('/dashboard');


    } catch (err: any) {
      const message = err.message || 'Ismeretlen hiba történt a bejelentkezés során.';
      setError(message);
    } finally {
      setIsLoading(false);
    }
  };

  // Ha a felhasználó már be van jelentkezve, a renderelés közben egy pillanatra még látszódhat a tartalom
  // de az useEffect rögtön átirányít. Kijelölhetjük, hogy ne rendereljen semmit, ha isAuthenticated:
  if (isAuthenticated) {
    return null;
  }

  return (
    <div style={{ maxWidth: '400px', margin: '50px auto', padding: '20px', border: '1px solid #ccc', borderRadius: '8px' }}>
      <h1>🔐 Bejelentkezés</h1>
      {error && <p style={{ color: 'red' }}>{error}</p>}

      <form onSubmit={handleSubmit}>
        <div style={{ marginBottom: '15px' }}>
          <label htmlFor="email">Email:</label>
          <input
            id="email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            style={{ width: '100%', padding: '8px', boxSizing: 'border-box' }}
          />
        </div>
        <div style={{ marginBottom: '15px' }}>
          <label htmlFor="password">Jelszó:</label>
          <input
            id="password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            style={{ width: '100%', padding: '8px', boxSizing: 'border-box' }}
          />
        </div>
        <button
          type="submit"
          disabled={isLoading}
          style={{ width: '100%', padding: '10px', backgroundColor: isLoading ? '#aaa' : '#007bff', color: 'white', border: 'none', borderRadius: '4px' }}
        >
          {isLoading ? 'Betöltés...' : 'Bejelentkezés'}
        </button>
      </form>
    </div>
  );
}
