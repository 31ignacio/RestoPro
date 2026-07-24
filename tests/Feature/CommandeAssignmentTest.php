<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandeAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_assigned_cook_can_manage_the_command(): void
    {
        $roleCuisinier = Role::create(['nom' => 'cuisinier', 'label' => 'Cuisinier']);
        $roleServeur = Role::create(['nom' => 'serveur', 'label' => 'Serveur']);

        $assignedCook = User::create([
            'name' => 'Cook One',
            'email' => 'cook1@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleCuisinier->id,
            'actif' => true,
        ]);

        $otherCook = User::create([
            'name' => 'Cook Two',
            'email' => 'cook2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleCuisinier->id,
            'actif' => true,
        ]);

        $server = User::create([
            'name' => 'Server',
            'email' => 'server@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleServeur->id,
            'actif' => true,
        ]);

        $commande = Commande::create([
            'numero' => 'CMD-TEST-0001',
            'user_id' => $server->id,
            'cuisinier_id' => $assignedCook->id,
            'statut' => 'en_attente',
            'type' => 'sur_place',
            'sous_total' => 0,
            'remise' => 0,
            'total' => 0,
        ]);

        $this->assertTrue($commande->canBeManagedBy($assignedCook));
        $this->assertFalse($commande->canBeManagedBy($otherCook));
    }

    public function test_unassigned_command_can_be_taken_by_any_cook(): void
    {
        $roleCuisinier = Role::create(['nom' => 'cuisinier', 'label' => 'Cuisinier']);
        $roleServeur = Role::create(['nom' => 'serveur', 'label' => 'Serveur']);

        $cook = User::create([
            'name' => 'Cook Three',
            'email' => 'cook3@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleCuisinier->id,
            'actif' => true,
        ]);

        $server = User::create([
            'name' => 'Server Two',
            'email' => 'server2@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleServeur->id,
            'actif' => true,
        ]);

        $commande = Commande::create([
            'numero' => 'CMD-TEST-0003',
            'user_id' => $server->id,
            'cuisinier_id' => null,
            'statut' => 'en_attente',
            'type' => 'sur_place',
            'sous_total' => 0,
            'remise' => 0,
            'total' => 0,
        ]);

        $this->assertTrue($commande->canBeManagedBy($cook));
    }

    public function test_delivery_command_can_store_livreur_and_delivery_fee(): void
    {
        $roleServeur = Role::create(['nom' => 'serveur', 'label' => 'Serveur']);
        $server = User::create([
            'name' => 'Server Delivery',
            'email' => 'delivery@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleServeur->id,
            'actif' => true,
        ]);

        $livreur = User::create([
            'name' => 'Livreur One',
            'email' => 'livreur1@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleServeur->id,
            'actif' => true,
        ]);

        $categorie = Categorie::create(['nom' => 'Plats', 'ordre' => 1]);
        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Burger',
            'prix' => 1500,
            'disponible' => true,
            'gerer_stock' => false,
            'ordre' => 1,
        ]);

        $this->actingAs($server);

        $response = $this->postJson('/commandes', [
            'type' => 'livraison',
            'livreur_id' => $livreur->id,
            'frais_livraison' => 1000,
            'items' => [[
                'produit_id' => $produit->id,
                'quantite' => 1,
            ]],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('commandes', [
            'livreur_id' => $livreur->id,
            'frais_livraison' => 1000,
        ]);
    }

    public function test_modifying_a_command_notifies_the_assigned_cook(): void
    {
        $roleCuisinier = Role::create(['nom' => 'cuisinier', 'label' => 'Cuisinier']);
        $roleServeur = Role::create(['nom' => 'serveur', 'label' => 'Serveur']);

        $assignedCook = User::create([
            'name' => 'Cook One',
            'email' => 'cook1@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleCuisinier->id,
            'actif' => true,
        ]);

        $server = User::create([
            'name' => 'Server',
            'email' => 'server@example.com',
            'password' => bcrypt('password'),
            'role_id' => $roleServeur->id,
            'actif' => true,
        ]);

        $categorie = Categorie::create(['nom' => 'Plats', 'ordre' => 1]);
        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Burger',
            'prix' => 1500,
            'disponible' => true,
            'gerer_stock' => false,
            'ordre' => 1,
        ]);

        $commande = Commande::create([
            'numero' => 'CMD-TEST-0002',
            'user_id' => $server->id,
            'cuisinier_id' => $assignedCook->id,
            'statut' => 'en_attente',
            'type' => 'sur_place',
            'sous_total' => 0,
            'remise' => 0,
            'total' => 0,
        ]);

        $this->actingAs($server);

        $response = $this->putJson('/commandes/'.$commande->id, [
            'items' => [[
                'produit_id' => $produit->id,
                'quantite' => 1,
            ]],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $assignedCook->id,
            'titre' => 'Modification de commande',
        ]);
    }
}
