// Next.js környezeti változó olvasása
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL;

/**
 * Általános API hívó funkció, amely kezeli a JWT tokent.
 * @param endpoint Az API végpont (pl. '/login_check', '/shopping_lists')
 * @param options A Fetch API beállításai
 * @param token A JWT token az Authorization fejlécbe
 */
export async function apiFetcher<T>(
  endpoint: string,
  options: RequestInit = {},
  token?: string | null
): Promise<T> {
  const url = `${API_BASE_URL}${endpoint}`;

  // A fejléc beállítása, hozzáadva a Bearer tokent
  const headers = {
    'Content-Type': 'application/json',
    ...(options.headers || {}),
    ...(token ? { 'Authorization': `Bearer ${token}` } : {})
  };

  const response = await fetch(url, {
    ...options,
    headers,
  });

  if (!response.ok) {
    // A 401-es hiba kezelése a Token frissítéséhez később
    if (response.status === 401) {
      console.error('Hitelesítési hiba, token frissítése szükséges lehet.');
    }

    // Dobunk egy hibát, ami a feljebb lévő kód kezeli
    const errorData = await response.json().catch(() => ({ message: response.statusText }));
    throw new Error(errorData.message || 'Hálózati hiba történt.');
  }

  // Ha a válasz 204 No Content (pl. DELETE), nem próbálunk JSON-t olvasni
  if (response.status === 204) {
    return null as T;
  }

  return response.json() as Promise<T>;
}
