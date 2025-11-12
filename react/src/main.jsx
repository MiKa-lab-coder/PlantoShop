import React from 'react';
import ReactDOM from 'react-dom/client';
import { createBrowserRouter, RouterProvider, Navigate } from 'react-router-dom';

import App from './App.jsx';
import HomePage from './pages/HomePage.jsx';
import LoginPage from './pages/LoginPage.jsx';
import RegisterPage from './pages/RegisterPage.jsx';
import ContactPage from './pages/ContactPage.jsx';
import CartPage from './pages/CartPage.jsx';
import ShopPage from './pages/ShopPage.jsx';
import PlantPage from "./pages/PlantPage.jsx";

// User Dashboard
import DashboardLayout from './components/DashboardLayout.jsx';
import UserProfilePage from './pages/UserProfilePage.jsx';
import UserOrdersPage from './pages/UserOrdersPage.jsx';
import UserOrdersDetailsPage from './pages/UserOrdersDetailsPage.jsx';
import UserSettingsPage from './pages/UserSettingsPage.jsx';

// Admin Dashboard
import AdminDashboardLayout from './components/AdminDashboardLayout.jsx';
import AdminPlantsPage from './pages/AdminPlantsPage.jsx';
import AdminOrdersPage from './pages/AdminOrdersPage.jsx';
import AdminUsersPage from './pages/AdminUsersPage.jsx';

import './index.css';

// Creation du ROUTEUR
const router = createBrowserRouter([
    {
        path: '/',
        element: <App />,
        children: [
            { path: '/', element: <HomePage /> },
            { path: '/login', element: <LoginPage /> },
            { path: '/register', element: <RegisterPage /> },
            { path: 'contact', element: <ContactPage /> },
            { path: 'cart', element: <CartPage /> },
            { path: 'shop', element: <ShopPage /> },
            { path: '/plant/:id', element: <PlantPage /> },
            
            // --- Routes de l'espace utilisateur ---
            {
                path: '/user',
                element: <DashboardLayout />,
                children: [
                    { path: '', element: <Navigate to="profile" replace /> }, 
                    { path: 'profile', element: <UserProfilePage /> },
                    { path: 'orders', element: <UserOrdersPage /> },
                    { path: 'orders/:id', element: <UserOrdersDetailsPage /> },
                    { path: 'settings', element: <UserSettingsPage /> },
                ]
            },
            // --- Routes de l'espace administrateur ---
            {
                path: '/admin',
                element: <AdminDashboardLayout />,
                children: [
                    { path: '', element: <Navigate to="plants" replace /> },
                    { path: 'plants', element: <AdminPlantsPage /> },
                    { path: 'orders', element: <AdminOrdersPage /> },
                    { path: 'users', element: <AdminUsersPage /> },
                ]
            }
        ],
    },
]);

ReactDOM.createRoot(document.getElementById('root')).render(
    <React.StrictMode>
        <RouterProvider router={router} />
    </React.StrictMode>
);
