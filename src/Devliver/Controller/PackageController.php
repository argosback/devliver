<?php

namespace Shapecode\Devliver\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Shapecode\Devliver\Entity\Package;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RepoController
 *
 * @package Shapecode\Devliver\Controller
 * @author  Nikita Loges
 *
 * @Route("/packages", name="devliver_package_")
 */
class PackageController extends Controller
{

    /**
     * @Route("", name="index")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Package::class);
        $qb = $repository->createQueryBuilder('p');

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $sort = $request->query->get('sort', 'p.id');
        $direction = $request->query->get('direction', 'asc');
        $filter = $request->query->get('filter');

        $qb->orderBy($sort, $direction);

        if (!empty($filter)) {
            $qb->andWhere($qb->expr()->like('p.name', ':filter'));
            $qb->setParameter('filter', '%' . $filter . '%');
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb, $page, $limit);

        return $this->render('@Devliver/Package/list.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/{package}/update", name="update")
     *
     * @param Request $request
     * @param Package $package
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request, Package $package)
    {
        $this->get('devliver.package_synchronization')->runUpdate($package);

        $this->get('session')->getFlashBag()->add('success', 'Package updated');

        if ($request->get('referer')) {
            $referer = $request->headers->get('referer');

            return $this->redirect($referer);
        }

        return $this->redirectToRoute('devliver_package_view', [
            'package' => $package->getId()
        ]);
    }

    /**
     * @Route("/{package}/view", name="view")
     *
     * @param Package $package
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function viewAction(Package $package)
    {
        if (!$package->getVersions()->count()) {
            return $this->redirectToRoute('devliver_package_update', [
                'package' => $package->getId()
            ]);
        }

        $packages = $package->getPackages();
        $stable = $package->getLastStablePackage();

        return $this->render('@Devliver/Package/view.html.twig', [
            'package'  => $package,
            'info'     => $stable,
            'versions' => $packages
        ]);
    }
}
