'use client';

import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { apiFetcher } from '@/api/fetcher'; // Feltételezi, hogy a fetcher.ts elérése helyes


// --- TÍPUSOK ---

interface AuthTokens {
  token: string;
  refresh_token: string;
}

interface AuthContextType {
  isAuthenticated: boolean;
  jwtToken: string | null;
  login: (email: string, password: string) => Promise<void>;
  logout: (redirect?: boolean) => void;
  refreshToken: () => Promise<void>;
  isReady: boolean; // Jelzi, hogy az initial localStorage betöltés megtörtént
}

// Inicializálás undefined-dal, hogy a useAuth hookban ellenőrizni lehessen
const AuthContext = createContext<AuthContextType | undefined>(undefined);


// --- HELPER FUNKCIÓK ---

/** Tokenek mentése localStorage-ba */
const saveTokens = (tokens: AuthTokens) => {
  localStorage.setItem('jwtToken', tokens.token);
  localStorage.setItem('refreshToken', tokens.refresh_token);
};

/** Tokenek törlése localStorage-ból */
const clearTokens = () => {
  localStorage.removeItem('jwtToken');
  localStorage.removeItem('refreshToken');
};


// --- AUTH PROVIDER KOMPONENS ---

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [jwtToken, setJwtToken] = useState<string | null>(null);
  const [refreshTokenValue, setRefreshTokenValue] = useState<string | null>(null);
  const [isReady, setIsReady] = useState(false); // Fontos a SSR/CSR szinkronizáláshoz
  const isAuthenticated = !!jwtToken;

  // A Next.js router importálása (kliens komponens)
  const { useRouter } = require('next/navigation');
  const router = useRouter();

  // 1. Inicializálás: Tokenek betöltése a localStorage-ból mount-kor
  useEffect(() => {
    // Csak a böngészőben fusson!
    if (typeof window !== 'undefined') {
      const storedJwt = localStorage.getItem('jwtToken');
      const storedRefresh = localStorage.getItem('refreshToken');

      if (storedJwt && storedRefresh) {
        setJwtToken(storedJwt);
        setRefreshTokenValue(storedRefresh);
      }
    }
    // Az inicializálás befejeződött
    setIsReady(true);
  }, []);

  // 2. Bejelentkezési funkció
  const login = useCallback(async (email: string, password: string): Promise<void> => {
    const data = { email, password };

    try {
      const tokens: AuthTokens = await apiFetcher('/login_check', {
        method: 'POST',
        body: JSON.stringify(data),
      });

      // Tokenek mentése a state-be és a localStorage-ba
      setJwtToken(tokens.token);
      setRefreshTokenValue(tokens.refresh_token);
      saveTokens(tokens);

    } catch (error) {
      console.error('Bejelentkezés sikertelen:', error);
      clearTokens();
      throw error;
    }
  }, []);

  // 3. Kijelentkezési funkció
  const logout = useCallback((redirect: boolean = true) => {
    setJwtToken(null);
    setRefreshTokenValue(null);
    clearTokens();
    if (redirect) {
      router.replace('/login');
    }
  }, [router]);

  // 4. Token Frissítési funkció
  const refreshToken = useCallback(async (): Promise<void> => {
    if (!refreshTokenValue) {
      // Ha nincs Refresh Token, ki kell jelentkezni
      logout(true);
      throw new Error('Refresh token is missing. Forced logout.');
    }

    try {
      const tokens: AuthTokens = await apiFetcher('/token/refresh', {
        method: 'POST',
        body: JSON.stringify({ refresh_token: refreshTokenValue }),
      });

      // Új tokenek mentése
      setJwtToken(tokens.token);
      setRefreshTokenValue(tokens.refresh_token); // A gesdinet/jwt-refresh-token-bundle új refresh tokent is adhat
      saveTokens(tokens);

    } catch (error) {
      // Ha a frissítés sikertelen (pl. lejárt a refresh token is), kényszerített kijelentkezés
      console.error('Token frissítés sikertelen. Kijelentkezés.', error);
      logout(true);
      throw error;
    }
  }, [refreshTokenValue, logout]);

  // Ha még nem vagyunk készen az inicializálással, ne rendereljünk semmit
  // Ezzel elkerüljük az SSR és CSR közötti eltérések okozta hibákat (Hydration Mismatch)
  if (!isReady) {
    return null; // Később ide tehető egy Loading spinner
  }

  return (
    <AuthContext.Provider value={{ isAuthenticated, jwtToken, login, logout, refreshToken, isReady }}>
      {children}
    </AuthContext.Provider>
  );
};


// --- EGYEDI HOOK ---

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
